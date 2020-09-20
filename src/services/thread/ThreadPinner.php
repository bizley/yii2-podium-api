<?php

declare(strict_types=1);

namespace bizley\podium\api\services\thread;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\PinEvent;
use bizley\podium\api\interfaces\PinnerInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;

final class ThreadPinner extends Component implements PinnerInterface
{
    public const EVENT_BEFORE_PINNING = 'podium.thread.pinning.before';
    public const EVENT_AFTER_PINNING = 'podium.thread.pinning.after';
    public const EVENT_BEFORE_UNPINNING = 'podium.thread.unpinning.before';
    public const EVENT_AFTER_UNPINNING = 'podium.thread.unpinning.after';

    /**
     * Calls before pinning the thread.
     */
    public function beforePin(): bool
    {
        $event = new PinEvent();
        $this->trigger(self::EVENT_BEFORE_PINNING, $event);

        return $event->canPin;
    }

    /**
     * Pins the thread.
     */
    public function pin(RepositoryInterface $thread): PodiumResponse
    {
        if (!$thread instanceof ThreadRepositoryInterface || !$this->beforePin()) {
            return PodiumResponse::error();
        }

        try {
            if (!$thread->pin()) {
                return PodiumResponse::error($thread->getErrors());
            }

            $this->afterPin($thread);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while pinning thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after pinning the thread successfully.
     */
    public function afterPin(ThreadRepositoryInterface $thread): void
    {
        $this->trigger(self::EVENT_AFTER_PINNING, new PinEvent(['repository' => $thread]));
    }

    /**
     * Calls before unpinning the thread.
     */
    public function beforeUnpin(): bool
    {
        $event = new PinEvent();
        $this->trigger(self::EVENT_BEFORE_UNPINNING, $event);

        return $event->canUnpin;
    }

    /**
     * Unpins the thread.
     */
    public function unpin(RepositoryInterface $thread): PodiumResponse
    {
        if (!$thread instanceof ThreadRepositoryInterface || !$this->beforeUnpin()) {
            return PodiumResponse::error();
        }

        try {
            if (!$thread->unpin()) {
                return PodiumResponse::error($thread->getErrors());
            }

            $this->afterUnpin($thread);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while unpinning thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after unpinning the thread successfully.
     */
    public function afterUnpin(ThreadRepositoryInterface $thread): void
    {
        $this->trigger(self::EVENT_AFTER_UNPINNING, new PinEvent(['repository' => $thread]));
    }
}
