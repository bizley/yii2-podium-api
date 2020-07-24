<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\PinEvent;
use bizley\podium\api\interfaces\PinnerInterface;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Class ThreadPinner
 * @package bizley\podium\api\models\thread
 */
class ThreadPinner extends Thread implements PinnerInterface
{
    public const EVENT_BEFORE_PINNING = 'podium.thread.pinning.before';
    public const EVENT_AFTER_PINNING = 'podium.thread.pinning.after';
    public const EVENT_BEFORE_UNPINNING = 'podium.thread.unpinning.before';
    public const EVENT_AFTER_UNPINNING = 'podium.thread.unpinning.after';

    /**
     * Adds TimestampBehavior.
     * @return array<string|int, mixed>
     */
    public function behaviors(): array
    {
        return ['timestamp' => TimestampBehavior::class];
    }

    /**
     * Executes before pin().
     * @return bool
     */
    public function beforePin(): bool
    {
        $event = new PinEvent();
        $this->trigger(self::EVENT_BEFORE_PINNING, $event);

        return $event->canPin;
    }

    /**
     * Pins the thread.
     * @return PodiumResponse
     */
    public function pin(): PodiumResponse
    {
        if (!$this->beforePin()) {
            return PodiumResponse::error();
        }

        $this->pinned = true;

        if (!$this->save()) {
            Yii::error(['Error while pinning thread', $this->errors], 'podium');

            return PodiumResponse::error($this);
        }

        $this->afterPin();

        return PodiumResponse::success();
    }

    /**
     * Executes after successful pin().
     */
    public function afterPin(): void
    {
        $this->trigger(self::EVENT_AFTER_PINNING, new PinEvent(['model' => $this]));
    }

    /**
     * Executes before unpin().
     * @return bool
     */
    public function beforeUnpin(): bool
    {
        $event = new PinEvent();
        $this->trigger(self::EVENT_BEFORE_UNPINNING, $event);

        return $event->canUnpin;
    }

    /**
     * Unpins the thread.
     * @return PodiumResponse
     */
    public function unpin(): PodiumResponse
    {
        if (!$this->beforeUnpin()) {
            return PodiumResponse::error();
        }

        $this->pinned = false;

        if (!$this->save()) {
            Yii::error(['Error while unpinning thread', $this->errors], 'podium');

            return PodiumResponse::error($this);
        }

        $this->afterUnpin();

        return PodiumResponse::success();
    }

    /**
     * Executes after successful unpin().
     */
    public function afterUnpin(): void
    {
        $this->trigger(self::EVENT_AFTER_UNPINNING, new PinEvent(['model' => $this]));
    }
}
