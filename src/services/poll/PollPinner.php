<?php

declare(strict_types=1);

namespace bizley\podium\api\services\poll;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\PinEvent;
use bizley\podium\api\interfaces\PinnerInterface;
use bizley\podium\api\interfaces\PollRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;

final class PollPinner extends Component implements PinnerInterface
{
    public const EVENT_BEFORE_PINNING = 'podium.poll.pinning.before';
    public const EVENT_AFTER_PINNING = 'podium.poll.pinning.after';
    public const EVENT_BEFORE_UNPINNING = 'podium.poll.unpinning.before';
    public const EVENT_AFTER_UNPINNING = 'podium.poll.unpinning.after';

    /**
     * Calls before pinning the poll.
     */
    public function beforePin(): bool
    {
        $event = new PinEvent();
        $this->trigger(self::EVENT_BEFORE_PINNING, $event);

        return $event->canPin;
    }

    /**
     * Pins the poll.
     */
    public function pin(RepositoryInterface $poll): PodiumResponse
    {
        if (!$poll instanceof PollRepositoryInterface || !$this->beforePin()) {
            return PodiumResponse::error();
        }

        try {
            if (!$poll->pin()) {
                return PodiumResponse::error($poll->getErrors());
            }

            $this->afterPin($poll);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while pinning poll', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after pinning the poll successfully.
     */
    public function afterPin(PollRepositoryInterface $poll): void
    {
        $this->trigger(self::EVENT_AFTER_PINNING, new PinEvent(['repository' => $poll]));
    }

    /**
     * Calls before unpinning the poll.
     */
    public function beforeUnpin(): bool
    {
        $event = new PinEvent();
        $this->trigger(self::EVENT_BEFORE_UNPINNING, $event);

        return $event->canUnpin;
    }

    /**
     * Unpins the poll.
     */
    public function unpin(RepositoryInterface $poll): PodiumResponse
    {
        if (!$poll instanceof PollRepositoryInterface || !$this->beforeUnpin()) {
            return PodiumResponse::error();
        }

        try {
            if (!$poll->unpin()) {
                return PodiumResponse::error($poll->getErrors());
            }

            $this->afterUnpin($poll);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while unpinning poll', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after unpinning the poll successfully.
     */
    public function afterUnpin(PollRepositoryInterface $poll): void
    {
        $this->trigger(self::EVENT_AFTER_UNPINNING, new PinEvent(['repository' => $poll]));
    }
}
