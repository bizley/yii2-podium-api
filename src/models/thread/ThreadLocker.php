<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\LockEvent;
use bizley\podium\api\interfaces\LockerInterface;
use bizley\podium\api\repos\ThreadRepo;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Class ThreadLocker
 * @package bizley\podium\api\models\thread
 */
class ThreadLocker extends ThreadRepo implements LockerInterface
{
    public const EVENT_BEFORE_LOCKING = 'podium.thread.locking.before';
    public const EVENT_AFTER_LOCKING = 'podium.thread.locking.after';
    public const EVENT_BEFORE_UNLOCKING = 'podium.thread.unlocking.before';
    public const EVENT_AFTER_UNLOCKING = 'podium.thread.unlocking.after';

    /**
     * Adds TimestampBehavior.
     * @return array<int|string, mixed>
     */
    public function behaviors(): array
    {
        return ['timestamp' => TimestampBehavior::class];
    }

    /**
     * @param int $modelId
     * @return LockerInterface|null
     */
    public static function findById(int $modelId): ?LockerInterface
    {
        return static::findOne(['id' => $modelId]);
    }

    /**
     * Executes before lock().
     * @return bool
     */
    public function beforeLock(): bool
    {
        $event = new LockEvent();
        $this->trigger(self::EVENT_BEFORE_LOCKING, $event);

        return $event->canLock;
    }

    /**
     * Locks the thread.
     * @return PodiumResponse
     */
    public function lock(): PodiumResponse
    {
        if (!$this->beforeLock()) {
            return PodiumResponse::error();
        }

        $this->locked = true;

        if (!$this->save()) {
            Yii::error(['Error while locking thread', $this->errors], 'podium');

            return PodiumResponse::error($this);
        }

        $this->afterLock();

        return PodiumResponse::success();
    }

    /**
     * Executes after successful lock().
     */
    public function afterLock(): void
    {
        $this->trigger(self::EVENT_AFTER_LOCKING, new LockEvent(['model' => $this]));
    }

    /**
     * Executes before unlock().
     * @return bool
     */
    public function beforeUnlock(): bool
    {
        $event = new LockEvent();
        $this->trigger(self::EVENT_BEFORE_UNLOCKING, $event);

        return $event->canUnlock;
    }

    /**
     * Unlocks the thread.
     * @return PodiumResponse
     */
    public function unlock(): PodiumResponse
    {
        if (!$this->beforeUnlock()) {
            return PodiumResponse::error();
        }

        $this->locked = false;

        if (!$this->save()) {
            Yii::error(['Error while unlocking thread', $this->errors], 'podium');

            return PodiumResponse::error($this);
        }

        $this->afterUnlock();

        return PodiumResponse::success();
    }

    /**
     * Executes after successful unlock().
     */
    public function afterUnlock(): void
    {
        $this->trigger(self::EVENT_AFTER_UNLOCKING, new LockEvent(['model' => $this]));
    }
}
