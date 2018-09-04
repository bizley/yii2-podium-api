<?php

declare(strict_types=1);

namespace bizley\podium\api\models\message;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\RemovableInterface;
use bizley\podium\api\repos\MessageParticipantRepo;
use bizley\podium\api\repos\MessageRepo;
use Yii;
use yii\db\Exception;

/**
 * Class MessageParticipantRemover
 * @package bizley\podium\api\models\message
 */
class MessageParticipantRemover extends MessageParticipantRepo implements RemovableInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.message.participant.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.message.participant.removing.after';

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

        if (!$this->archived) {
            $this->addError('archived', Yii::t('podium.error', 'message.must.be.archived'));
            return PodiumResponse::error($this);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ((int)static::find()->where(['message_id' => $this->message_id])->count() === 1) {

                if ($this->delete() === false) {
                    Yii::error('Error while deleting message participany copy', 'podium');
                    throw new Exception('Error while deleting message participany copy!');
                }

                $messageRepo = MessageRepo::findOne($this->message_id);
                if ($messageRepo === null || $messageRepo->delete() === false) {
                    Yii::error('Error while deleting message', 'podium');
                    throw new Exception('Error while deleting message!');
                }

                $this->afterRemove();

                $transaction->commit();

                return PodiumResponse::success();
            }

            if ($this->delete() === false) {
                Yii::error('Error while deleting message participany copy', 'podium');
                throw new Exception('Error while deleting message participany copy!');
            }

            $this->afterRemove();

            $transaction->commit();

            return PodiumResponse::success();

        } catch (\Throwable $exc) {
            Yii::error(['Exception while removing message', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            try {
                $transaction->rollBack();
            } catch (\Throwable $excTrans) {
                Yii::error(['Exception while message removing transaction rollback', $excTrans->getMessage(), $excTrans->getTraceAsString()], 'podium');
            }
            return PodiumResponse::error();
        }
    }

    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
