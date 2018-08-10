<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\events\LockEvent;
use bizley\podium\api\interfaces\LockableInterface;
use bizley\podium\api\repos\ThreadRepo;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Class ThreadLocker
 * @package bizley\podium\api\models\thread
 */
class ThreadLocker extends ThreadRepo implements LockableInterface
{
    public const EVENT_BEFORE_LOCKING = 'podium.thread.locking.before';
    public const EVENT_AFTER_LOCKING = 'podium.thread.locking.after';
    public const EVENT_BEFORE_UNLOCKING = 'podium.thread.unlocking.before';
    public const EVENT_AFTER_UNLOCKING = 'podium.thread.unlocking.after';

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
     * @return bool
     */
    public function beforeLock(): bool
    {
        $event = new LockEvent();
        $this->trigger(self::EVENT_BEFORE_LOCKING, $event);

        return $event->canLock;
    }

    /**
     * @return bool
     */
    public function lock(): bool
    {
        if (!$this->beforeLock()) {
            return false;
        }

        $this->locked = true;
        if (!$this->save(false)) {
            Yii::error(['thread.lock', $this->errors], 'podium');
            return false;
        }

        $this->afterLock();
        return true;
    }

    public function afterLock(): void
    {
        $this->trigger(self::EVENT_AFTER_LOCKING, new LockEvent([
            'model' => $this
        ]));
    }

    /**
     * @return bool
     */
    public function beforeUnlock(): bool
    {
        $event = new LockEvent();
        $this->trigger(self::EVENT_BEFORE_UNLOCKING, $event);

        return $event->canUnlock;
    }

    /**
     * @return bool
     */
    public function unlock(): bool
    {
        if (!$this->beforeUnlock()) {
            return false;
        }

        $this->locked = false;
        if (!$this->save(false)) {
            Yii::error(['thread.unlock', $this->errors], 'podium');
            return false;
        }

        $this->afterUnlock();
        return true;
    }

    public function afterUnlock(): void
    {
        $this->trigger(self::EVENT_AFTER_UNLOCKING, new LockEvent([
            'model' => $this
        ]));
    }
}
