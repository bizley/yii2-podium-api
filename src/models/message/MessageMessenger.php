<?php

declare(strict_types=1);

namespace bizley\podium\api\models\message;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\enums\MessageSide;
use bizley\podium\api\enums\MessageStatus;
use bizley\podium\api\events\MessageEvent;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\MessageFormInterface;
use bizley\podium\api\interfaces\MessageParticipantModelInterface;
use bizley\podium\api\interfaces\MessengerInterface;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\db\Transaction;
use yii\di\Instance;

/**
 * Class MessageMessenger
 * @package bizley\podium\api\models\message
 */
class MessageMessenger extends Message implements MessengerInterface
{
    public const EVENT_BEFORE_SENDING = 'podium.message.sending.before';
    public const EVENT_AFTER_SENDING = 'podium.message.sending.after';

    /**
     * @var string|array|object|MessageFormInterface message form handler
     * Component ID, class, configuration array, or instance of MessageFormInterface.
     */
    public $messageFormHandler = \bizley\podium\api\models\message\MessageForm::class;

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->messageFormHandler = Instance::ensure($this->messageFormHandler, MessageFormInterface::class);
    }

    /**
     * @return MessageFormInterface
     */
    public function getForm(): MessageFormInterface
    {
        return new $this->messageFormHandler();
    }

    private ?int $senderId = null;

    /**
     * @param MembershipInterface $sender
     * @throws InsufficientDataException
     */
    public function setSender(MembershipInterface $sender): void
    {
        $senderId = $sender->getId();
        if ($senderId === null) {
            throw new InsufficientDataException('Missing sender Id for message messenger');
        }
        $this->senderId = $senderId;
    }

    /**
     * @return int|null
     */
    public function getSender(): ?int
    {
        return $this->senderId;
    }

    private ?int $receiverId = null;

    /**
     * @param MembershipInterface $receiver
     */
    public function setReceiver(MembershipInterface $receiver): void
    {
        $this->receiverId = $receiver->getId();
    }

    /**
     * @return int|null
     */
    public function getReceiver(): ?int
    {
        return $this->receiverId;
    }

    private ?MessageParticipantModelInterface $replyTo = null;

    /**
     * @param MessageParticipantModelInterface|null $replyTo
     */
    public function setReplyTo(?MessageParticipantModelInterface $replyTo): void
    {
        if ($replyTo) {
            $this->replyTo = $replyTo;
        }
    }

    /**
     * @return MessageParticipantModelInterface|null
     */
    public function getReplyTo(): ?MessageParticipantModelInterface
    {
        return $this->replyTo;
    }

    /**
     * @return array
     */
    public function behaviors(): array
    {
        return ['timestamp' => TimestampBehavior::class];
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['subject', 'content'], 'required'],
            [['subject', 'content'], 'string', 'min' => 3],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'content' => Yii::t('podium.label', 'message.content'),
            'subject' => Yii::t('podium.label', 'message.subject'),
        ];
    }

    /**
     * @param array $data
     * @return bool
     */
    public function loadData(array $data = []): bool
    {
        return $this->load($data, '');
    }

    /**
     * @return bool
     */
    public function beforeSend(): bool
    {
        $event = new MessageEvent();
        $this->trigger(self::EVENT_BEFORE_SENDING, $event);

        return $event->canSend;
    }

    /**
     * @return PodiumResponse
     */
    public function send(): PodiumResponse
    {
        if (!$this->beforeSend()) {
            return PodiumResponse::error();
        }

        $replyTo = $this->getReplyTo();
        if ($replyTo) {
            if ($replyTo->getSideId() !== MessageSide::SENDER || $this->getReceiver() !== $replyTo->getMemberId()) {
                $this->addError('reply_to_id', Yii::t('podium.error', 'message.wrong.reply'));

                return PodiumResponse::error($this);
            }

            $replyMessage = $replyTo->getParent();
            if ($replyMessage) {
                $this->reply_to_id = $replyMessage->getId();
            }
        }

        if (!$this->validate()) {
            return PodiumResponse::error($this);
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->save(false)) {
                throw new Exception('Error while creating message!');
            }

            $sender = $this->getSender();
            if ($sender === null) {
                throw new Exception('Can not find sender for message messenger!');
            }
            $receiver = $this->getReceiver();
            if ($receiver === null) {
                throw new Exception('Can not find receiver for message messenger!');
            }

            if ($this->reply_to_id !== null) {
                $messageForm = $this->getForm();

                $messageToReply = $messageForm->findMessageToReply($sender, $this->reply_to_id);
                if ($messageToReply === null) {
                    throw new Exception('Can not find message participant copy to change its status!');
                }

                if (!$messageToReply->markReplied()->result) {
                    throw new Exception('Error while marking message participant copy as replied!');
                }
            }

            $senderCopy = $this->getForm();
            $senderCopy->setSenderId($sender);
            $senderCopy->setMessageId($this->id);
            $senderCopy->setStatusId(MessageStatus::READ);
            $senderCopy->setSideId(MessageSide::SENDER);
            if (!$senderCopy->create()->result) {
                throw new Exception('Error while creating sender message copy!');
            }

            $receiverCopy = $this->getForm();
            $receiverCopy->setSenderId($receiver);
            $receiverCopy->setMessageId($this->id);
            $receiverCopy->setStatusId(MessageStatus::NEW);
            $receiverCopy->setSideId(MessageSide::RECEIVER);
            if (!$receiverCopy->create()->result) {
                throw new Exception('Error while creating receiver message copy!');
            }

            $this->afterSend();
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while creating message', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterSend(): void
    {
        $this->trigger(self::EVENT_AFTER_SENDING, new MessageEvent(['model' => $this]));
    }

    /**
     * Updates model.
     * @return PodiumResponse
     * @throws NotSupportedException
     */
    public function edit(): PodiumResponse
    {
        throw new NotSupportedException('Message can not be edited.');
    }

    /**
     * Creates model.
     * @return PodiumResponse
     * @throws NotSupportedException
     */
    public function create(): PodiumResponse
    {
        throw new NotSupportedException('Use send() to create message.');
    }
}
