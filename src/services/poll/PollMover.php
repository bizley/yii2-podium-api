<?php

declare(strict_types=1);

namespace bizley\podium\api\services\poll;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\MoveEvent;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\interfaces\PollRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;

final class PollMover extends Component implements MoverInterface
{
    public const EVENT_BEFORE_MOVING = 'podium.poll.moving.before';
    public const EVENT_AFTER_MOVING = 'podium.poll.moving.after';

    public function beforeMove(): bool
    {
        $event = new MoveEvent();
        $this->trigger(self::EVENT_BEFORE_MOVING, $event);

        return $event->canMove;
    }

    /**
     * Moves the poll to another thread.
     */
    public function move(RepositoryInterface $poll, RepositoryInterface $thread): PodiumResponse
    {
        if (
            !$poll instanceof PollRepositoryInterface
            || !$thread instanceof ThreadRepositoryInterface
            || !$this->beforeMove()
        ) {
            return PodiumResponse::error();
        }

        try {
            if ($thread->hasPoll()) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'thread.has.poll')]);
            }

            if (!$poll->move($thread)) {
                return PodiumResponse::error($thread->getErrors());
            }

            $this->afterMove($poll);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while moving poll', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterMove(PollRepositoryInterface $poll): void
    {
        $this->trigger(self::EVENT_AFTER_MOVING, new MoveEvent(['repository' => $poll]));
    }
}
