<?php

declare(strict_types=1);

namespace bizley\podium\api\services\thread;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\LockEvent;
use bizley\podium\api\interfaces\LockerInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\repositories\ThreadRepository;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class ThreadLocker extends Component implements LockerInterface
{
    public const EVENT_BEFORE_LOCKING = 'podium.thread.locking.before';
    public const EVENT_AFTER_LOCKING = 'podium.thread.locking.after';
    public const EVENT_BEFORE_UNLOCKING = 'podium.thread.unlocking.before';
    public const EVENT_AFTER_UNLOCKING = 'podium.thread.unlocking.after';

    private ?ThreadRepositoryInterface $thread = null;

    /**
     * @var string|array|ThreadRepositoryInterface
     */
    public $repositoryConfig = ThreadRepository::class;

    /**
     * @return ThreadRepositoryInterface
     * @throws InvalidConfigException
     */
    private function getThread(): ThreadRepositoryInterface
    {
        if ($this->thread === null) {
            /** @var ThreadRepositoryInterface $thread */
            $thread = Instance::ensure($this->repositoryConfig, ThreadRepositoryInterface::class);
            $this->thread = $thread;
        }
        return $this->thread;
    }

    public function beforeLock(): bool
    {
        $event = new LockEvent();
        $this->trigger(self::EVENT_BEFORE_LOCKING, $event);

        return $event->canLock;
    }

    /**
     * Locks the thread.
     * @param int $id
     * @return PodiumResponse
     * @throws InvalidConfigException
     */
    public function lock(int $id): PodiumResponse
    {
        if (!$this->beforeLock()) {
            return PodiumResponse::error();
        }

        $thread = $this->getThread();
        if (!$thread->find($id)) {
            return PodiumResponse::error(['api' => Yii::t('podium.error', 'thread.not.exists')]);
        }

        if (!$thread->lock()) {
            Yii::error(['Error while locking thread', $thread->getErrors()], 'podium');

            return PodiumResponse::error($thread->getErrors());
        }

        $this->afterLock();

        return PodiumResponse::success();
    }

    public function afterLock(): void
    {
        $this->trigger(self::EVENT_AFTER_LOCKING, new LockEvent(['model' => $this]));
    }

    public function beforeUnlock(): bool
    {
        $event = new LockEvent();
        $this->trigger(self::EVENT_BEFORE_UNLOCKING, $event);

        return $event->canUnlock;
    }

    /**
     * Unlocks the thread.
     * @param int $id
     * @return PodiumResponse
     * @throws InvalidConfigException
     */
    public function unlock(int $id): PodiumResponse
    {
        if (!$this->beforeUnlock()) {
            return PodiumResponse::error();
        }

        $thread = $this->getThread();
        if (!$thread->find($id)) {
            return PodiumResponse::error(['api' => Yii::t('podium.error', 'thread.not.exists')]);
        }

        if (!$thread->unlock()) {
            Yii::error(['Error while unlocking thread', $thread->getErrors()], 'podium');

            return PodiumResponse::error($thread->getErrors());
        }

        $this->afterUnlock();

        return PodiumResponse::success();
    }

    public function afterUnlock(): void
    {
        $this->trigger(self::EVENT_AFTER_UNLOCKING, new LockEvent(['model' => $this]));
    }
}
