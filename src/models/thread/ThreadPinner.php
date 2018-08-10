<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\events\PinEvent;
use bizley\podium\api\interfaces\PinnableInterface;
use bizley\podium\api\repos\ThreadRepo;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Class ThreadPinner
 * @package bizley\podium\api\models\thread
 */
class ThreadPinner extends ThreadRepo implements PinnableInterface
{
    public const EVENT_BEFORE_PINNING = 'podium.thread.pinning.before';
    public const EVENT_AFTER_PINNING = 'podium.thread.pinning.after';
    public const EVENT_BEFORE_UNPINNING = 'podium.thread.unpinning.before';
    public const EVENT_AFTER_UNPINNING = 'podium.thread.unpinning.after';

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
    public function beforePin(): bool
    {
        $event = new PinEvent();
        $this->trigger(self::EVENT_BEFORE_PINNING, $event);

        return $event->canPin;
    }

    /**
     * @return bool
     */
    public function pin(): bool
    {
        if (!$this->beforePin()) {
            return false;
        }

        $this->pinned = true;
        if (!$this->save(false)) {
            Yii::error(['thread.pin', $this->errors], 'podium');
            return false;
        }

        $this->afterPin();
        return true;
    }

    public function afterPin(): void
    {
        $this->trigger(self::EVENT_AFTER_PINNING, new PinEvent([
            'model' => $this
        ]));
    }

    /**
     * @return bool
     */
    public function beforeUnpin(): bool
    {
        $event = new PinEvent();
        $this->trigger(self::EVENT_BEFORE_UNPINNING, $event);

        return $event->canUnpin;
    }

    /**
     * @return bool
     */
    public function unpin(): bool
    {
        if (!$this->beforeUnpin()) {
            return false;
        }

        $this->pinned = false;
        if (!$this->save(false)) {
            Yii::error(['thread.unpin', $this->errors], 'podium');
            return false;
        }

        $this->afterUnpin();
        return true;
    }

    public function afterUnpin(): void
    {
        $this->trigger(self::EVENT_AFTER_UNPINNING, new PinEvent([
            'model' => $this
        ]));
    }
}
