<?php

declare(strict_types=1);

namespace bizley\podium\api\models\message;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\MessageEvent;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\MessageFormInterface;
use bizley\podium\api\repos\MessageRepo;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;

/**
 * Class MessageForm
 * @package bizley\podium\api\models\message
 */
class MessageForm extends MessageRepo implements MessageFormInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.message.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.message.creating.after';

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

    /**
     * @return array
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => TimestampBehavior::class,
        ];
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
     * @param array|null $data
     * @return bool
     */
    public function loadData(?array $data = null): bool
    {
        return $this->load($data, '');
    }

    /**
     * @return bool
     */
    public function beforeCreate(): bool
    {
        $event = new MessageEvent();
        $this->trigger(self::EVENT_BEFORE_CREATING, $event);

        return $event->canCreate;
    }

    /**
     * @return PodiumResponse
     */
    public function create(): PodiumResponse
    {
        if (!$this->beforeCreate()) {
            return PodiumResponse::error();
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->save()) {
                Yii::error(['Error while creating message', $this->errors], 'podium');
                throw new Exception('Error while creating message!');
            }



            $this->afterCreate();

            $transaction->commit();
            return PodiumResponse::success();

        } catch (\Throwable $exc) {
            Yii::error(['Exception while creating message', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            try {
                $transaction->rollBack();
            } catch (\Throwable $excTrans) {
                Yii::error(['Exception while message creating transaction rollback', $excTrans->getMessage(), $excTrans->getTraceAsString()], 'podium');
            }
        }
        return PodiumResponse::error($this);
    }

    public function afterCreate(): void
    {
        $this->trigger(self::EVENT_AFTER_CREATING, new MessageEvent([
            'model' => $this
        ]));
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
}
