<?php

declare(strict_types=1);

namespace bizley\podium\api\models\message;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\MessageRemoverInterface;
use bizley\podium\api\interfaces\ModelInterface;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;

/**
 * Class MessageParticipantRemover
 * @package bizley\podium\api\models\message
 */
class MessageRemover extends MessageParticipant implements MessageRemoverInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.message.participant.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.message.participant.removing.after';

    private $_messageHandler;

    /**
     * @param ModelInterface $messageHandler
     */
    public function setMessageHandler(ModelInterface $messageHandler): void
    {
        $this->_messageHandler = $messageHandler;
    }

    /**
     * @return ModelInterface|null
     */
    public function getMessageHandler(): ?ModelInterface
    {
        return $this->_messageHandler;
    }

    /**
     * @param int $messageId
     * @param MembershipInterface $participant
     * @return MessageRemoverInterface|null
     */
    public static function findByMessageIdAndParticipant(int $messageId, MembershipInterface $participant): ?MessageRemoverInterface
    {
        return static::findOne([
            'message_id' => $messageId,
            'member_id' => $participant->getId(),
        ]);
    }

    /**
     * @return bool
     */
    public function beforeRemove(): bool
    {
        $event = new RemoveEvent();
        $this->trigger(self::EVENT_BEFORE_REMOVING, $event);

        return $event->canRemove;
    }

    /**
     * @return PodiumResponse
     */
    public function remove(): PodiumResponse
    {
        if (!$this->beforeRemove()) {
            return PodiumResponse::error();
        }

        if (!$this->isArchived()) {
            $this->addError('archived', Yii::t('podium.error', 'message.must.be.archived'));

            return PodiumResponse::error($this);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ((int)static::find()->where(['message_id' => $this->message_id])->count() === 1) {
                if ($this->delete() === false) {
                    Yii::error('Error while deleting message copy', 'podium');

                    throw new Exception('Error while deleting message copy!');
                }

                $messageClass = $this->getMessageHandler();

                if ($messageClass === null) {
                    throw new InvalidConfigException('MessageHandler must be set in order to remove message completely.');
                }

                $messageRepo = $messageClass::findById($this->message_id);
                if ($messageRepo === null || $messageRepo->delete() === false) {
                    Yii::error('Error while deleting message', 'podium');

                    throw new Exception('Error while deleting message!');
                }

                $this->afterRemove();
                $transaction->commit();

                return PodiumResponse::success();
            }

            if ($this->delete() === false) {
                Yii::error('Error while deleting message copy', 'podium');

                throw new Exception('Error while deleting message copy!');
            }

            $this->afterRemove();
            $transaction->commit();

            return PodiumResponse::success();

        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while removing message', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
