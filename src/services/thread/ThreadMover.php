<?php

declare(strict_types=1);

namespace bizley\podium\api\services\thread;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\MoveEvent;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;
use yii\db\Exception;
use yii\db\Transaction;

final class ThreadMover extends Component implements MoverInterface
{
    public const EVENT_BEFORE_MOVING = 'podium.thread.moving.before';
    public const EVENT_AFTER_MOVING = 'podium.thread.moving.after';

    public function beforeMove(): bool
    {
        $event = new MoveEvent();
        $this->trigger(self::EVENT_BEFORE_MOVING, $event);

        return $event->canMove;
    }

    /**
     * Moves the thread to another forum.
     */
    public function move(RepositoryInterface $thread, RepositoryInterface $forum): PodiumResponse
    {
        if (
            !$thread instanceof ThreadRepositoryInterface
            || !$forum instanceof ForumRepositoryInterface
            || !$this->beforeMove()
        ) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$thread->move($forum)) {
                return PodiumResponse::error($thread->getErrors());
            }

            $postsCount = $thread->getPostsCount();

            /** @var ForumRepositoryInterface $threadParent */
            $threadParent = $thread->getParent();
            if (!$threadParent->updateCounters(-1, -$postsCount)) {
                throw new Exception('Error while updating old forum counters!');
            }
            if (!$forum->updateCounters(1, $postsCount)) {
                throw new Exception('Error while updating new forum counters!');
            }

            $this->afterMove($thread);
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while moving thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterMove(ThreadRepositoryInterface $thread): void
    {
        $this->trigger(self::EVENT_AFTER_MOVING, new MoveEvent(['repository' => $thread]));
    }
}
