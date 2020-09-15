<?php

declare(strict_types=1);

namespace bizley\podium\api\services\forum;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\MoveEvent;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;

final class ForumMover extends Component implements MoverInterface
{
    public const EVENT_BEFORE_MOVING = 'podium.forum.moving.before';
    public const EVENT_AFTER_MOVING = 'podium.forum.moving.after';

    public function beforeMove(): bool
    {
        $event = new MoveEvent();
        $this->trigger(self::EVENT_BEFORE_MOVING, $event);

        return $event->canMove;
    }

    /**
     * Moves the forum to another category.
     */
    public function move(RepositoryInterface $forum, RepositoryInterface $category): PodiumResponse
    {
        if (
            !$forum instanceof ForumRepositoryInterface
            || !$category instanceof CategoryRepositoryInterface
            || !$this->beforeMove()
        ) {
            return PodiumResponse::error();
        }

        try {
            if (!$forum->move($category)) {
                return PodiumResponse::error($forum->getErrors());
            }

            $this->afterMove($forum);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while moving forum', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    public function afterMove(ForumRepositoryInterface $forum): void
    {
        $this->trigger(self::EVENT_AFTER_MOVING, new MoveEvent(['repository' => $forum]));
    }
}
