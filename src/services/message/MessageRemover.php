<?php

declare(strict_types=1);

namespace bizley\podium\api\services\message;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\MessageRemoverInterface;
use bizley\podium\api\interfaces\MessageRepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;
use yii\db\Exception;
use yii\db\Transaction;

final class MessageRemover extends Component implements MessageRemoverInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.message.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.message.removing.after';

    public function beforeRemove(): bool
    {
        $event = new RemoveEvent();
        $this->trigger(self::EVENT_BEFORE_REMOVING, $event);

        return $event->canRemove;
    }

    public function remove(MessageRepositoryInterface $message, MemberRepositoryInterface $participant): PodiumResponse
    {
        if (!$this->beforeRemove()) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $messageSide = $message->getParticipant($participant);

            if (!$messageSide->isArchived()) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'message.must.be.archived')]);
            }

            if (!$messageSide->delete()) {
                return PodiumResponse::error();
            }

            if ($message->isCompletelyDeleted() && !$message->delete()) {
                throw new Exception('Error while deleting the message!');
            }

            $transaction->commit();
            $this->afterRemove();

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
