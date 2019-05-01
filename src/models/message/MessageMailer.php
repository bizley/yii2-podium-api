<?php

declare(strict_types=1);

namespace bizley\podium\api\models\message;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\enums\MessageSide;
use bizley\podium\api\enums\MessageStatus;
use bizley\podium\api\events\MessageEvent;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\MessageParticipantModelInterface;
use bizley\podium\api\interfaces\SendingInterface;
use Throwable;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;

/**
 * Class MessageMailer
 * @package bizley\podium\api\models\message
 */
class MessageMailer extends Message implements SendingInterface
{
    public const EVENT_BEFORE_SENDING = 'podium.message.sending.before';
    public const EVENT_AFTER_SENDING = 'podium.message.sending.after';

    private $_senderId;

    /**
     * @param MembershipInterface $sender
     */
    public function setSender(MembershipInterface $sender): void
    {
        $this->_senderId = $sender->getId();
    }

    /**
     * @return int
     */
    public function getSender(): int
    {
        return $this->_senderId;
    }

    private $_receiverId;

    /**
     * @param MembershipInterface $receiver
     */
    public function setReceiver(MembershipInterface $receiver): void
    {
        $this->_receiverId = $receiver->getId();
    }

    /**
     * @return int
     */
    public function getReceiver(): int
    {
        return $this->_receiverId;
    }

    private $_replyTo;

    /**
     * @param MessageParticipantModelInterface|null $replyTo
     */
    public function setReplyTo(?MessageParticipantModelInterface $replyTo): void
    {
        if ($replyTo) {
            $this->_replyTo = $replyTo;
        }
    }

    /**
     * @return MessageParticipantModelInterface|null
     */
    public function getReplyTo(): ?MessageParticipantModelInterface
    {
        return $this->_replyTo;
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

        if (
            $this->getReplyTo()
            && (
                $this->getReceiver() !== $this->getReplyTo()->getMemberId()
                || $this->getReplyTo()->getSideId() !== MessageSide::SENDER
            )
        ) {
            $this->addError('reply_to_id', Yii::t('podium.error', 'message.wrong.reply'));

            return PodiumResponse::error($this);
        }

        $this->reply_to_id = $this->getReplyTo() ? $this->getReplyTo()->getParent()->getId() : null;

        if (!$this->validate()) {
            return PodiumResponse::error($this);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->save(false)) {
                throw new Exception('Error while creating message!');
            }

            if ($this->reply_to_id !== null) {
                // TODO: make configurable
                $repliedMessage = MessageForm::findOne([
                    'member_id' => $this->getSender(),
                    'message_id' => $this->reply_to_id,
                ]);
                if ($repliedMessage === null) {
                    throw new Exception('Can not find message participant copy to change its status!');
                }

                if (!$repliedMessage->markReplied()->result) {
                    throw new Exception('Error while marking message participant copy as replied!');
                }
            }

            $senderCopy = new MessageForm([
                'member_id' => $this->getSender(),
                'message_id' => $this->id,
                'status_id' => MessageStatus::READ,
                'side_id' => MessageSide::SENDER,
            ]);
            if (!$senderCopy->create()->result) {
                throw new Exception('Error while creating sender message copy!');
            }

            $receiverCopy = new MessageForm([
                'member_id' => $this->getReceiver(),
                'message_id' => $this->id,
                'status_id' => MessageStatus::NEW,
                'side_id' => MessageSide::RECEIVER,
            ]);
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
        throw new NotSupportedException('Use send to create message.');
    }
}
