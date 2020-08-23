<?php

declare(strict_types=1);

namespace bizley\podium\api\services\thread;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\LockEvent;
use bizley\podium\api\interfaces\LockerInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;

final class ThreadLocker extends Component implements LockerInterface
{
    public const EVENT_BEFORE_LOCKING = 'podium.thread.locking.before';
    public const EVENT_AFTER_LOCKING = 'podium.thread.locking.after';
    public const EVENT_BEFORE_UNLOCKING = 'podium.thread.unlocking.before';
    public const EVENT_AFTER_UNLOCKING = 'podium.thread.unlocking.after';

    public function beforeLock(): bool
    {
        $event = new LockEvent();
        $this->trigger(self::EVENT_BEFORE_LOCKING, $event);

        return $event->canLock;
    }

    /**
     * Locks the thread.
     */
    public function lock(ThreadRepositoryInterface $thread): PodiumResponse
    {
        if (!$this->beforeLock()) {
            return PodiumResponse::error();
        }

        try {
            if (!$thread->lock()) {
                return PodiumResponse::error($thread->getErrors());
            }

            $this->afterLock($thread);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while locking thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterLock(ThreadRepositoryInterface $thread): void
    {
        $this->trigger(self::EVENT_AFTER_LOCKING, new LockEvent(['repository' => $thread]));
    }

    public function beforeUnlock(): bool
    {
        $event = new LockEvent();
        $this->trigger(self::EVENT_BEFORE_UNLOCKING, $event);

        return $event->canUnlock;
    }

    /**
     * Unlocks the thread.
     */
    public function unlock(ThreadRepositoryInterface $thread): PodiumResponse
    {
        if (!$this->beforeUnlock()) {
            return PodiumResponse::error();
        }

        try {
            if (!$thread->unlock()) {
                return PodiumResponse::error($thread->getErrors());
            }

            $this->afterUnlock($thread);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while unlocking thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterUnlock(ThreadRepositoryInterface $thread): void
    {
        $this->trigger(self::EVENT_AFTER_UNLOCKING, new LockEvent(['repository' => $thread]));
    }
}
