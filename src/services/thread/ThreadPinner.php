<?php

declare(strict_types=1);

namespace bizley\podium\api\services\thread;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\PinEvent;
use bizley\podium\api\interfaces\PinnerInterface;
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

    public function beforePin(): bool
    {
        $event = new PinEvent();
        $this->trigger(self::EVENT_BEFORE_PINNING, $event);

        return $event->canPin;
    }

    public function pin(ThreadRepositoryInterface $thread): PodiumResponse
    {
        if (!$this->beforePin()) {
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

            return PodiumResponse::error();
        }
    }

    public function afterPin(ThreadRepositoryInterface $thread): void
    {
        $this->trigger(self::EVENT_AFTER_PINNING, new PinEvent(['repository' => $thread]));
    }

    public function beforeUnpin(): bool
    {
        $event = new PinEvent();
        $this->trigger(self::EVENT_BEFORE_UNPINNING, $event);

        return $event->canUnpin;
    }

    public function unpin(ThreadRepositoryInterface $thread): PodiumResponse
    {
        if (!$this->beforeUnpin()) {
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

            return PodiumResponse::error();
        }
    }

    public function afterUnpin(ThreadRepositoryInterface $thread): void
    {
        $this->trigger(self::EVENT_AFTER_UNPINNING, new PinEvent(['repository' => $thread]));
    }
}
