<?php

declare(strict_types=1);

namespace bizley\podium\api\services\thread;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;
use yii\db\Exception;
use yii\db\Transaction;

final class ThreadRemover extends Component implements RemoverInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.thread.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.thread.removing.after';

    /**
     * Calls before removing the thread.
     */
    public function beforeRemove(): bool
    {
        $event = new RemoveEvent();
        $this->trigger(self::EVENT_BEFORE_REMOVING, $event);

        return $event->canRemove;
    }

    /**
     * Removes the thread.
     */
    public function remove(RepositoryInterface $thread): PodiumResponse
    {
        if (!$thread instanceof ThreadRepositoryInterface || !$this->beforeRemove()) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$thread->isArchived()) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'thread.must.be.archived')]);
            }

            if (!$thread->delete()) {
                return PodiumResponse::error();
            }

            /** @var ForumRepositoryInterface $forum */
            $forum = $thread->getParent();
            if (!$forum->updateCounters(-1, -$thread->getPostsCount())) {
                throw new Exception('Error while updating forum counters!');
            }

            $this->afterRemove();
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while deleting thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after removing the thread successfully.
     */
    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
