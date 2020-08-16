<?php

declare(strict_types=1);

namespace bizley\podium\api\services\poll;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\MoveEvent;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\interfaces\PollRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\repositories\PollRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class PollMover extends Component implements MoverInterface
{
    public const EVENT_BEFORE_MOVING = 'podium.poll.moving.before';
    public const EVENT_AFTER_MOVING = 'podium.poll.moving.after';

    private ?PollRepositoryInterface $poll = null;

    /**
     * @var string|array|PollRepositoryInterface
     */
    public $repositoryConfig = PollRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getPoll(): PollRepositoryInterface
    {
        if (null === $this->poll) {
            /** @var PollRepositoryInterface $poll */
            $poll = Instance::ensure($this->repositoryConfig, PollRepositoryInterface::class);
            $this->poll = $poll;
        }

        return $this->poll;
    }

    public function beforeMove(): bool
    {
        $event = new MoveEvent();
        $this->trigger(self::EVENT_BEFORE_MOVING, $event);

        return $event->canMove;
    }

    /**
     * Moves the poll to another thread.
     */
    public function move($id, RepositoryInterface $thread): PodiumResponse
    {
        if (!$thread instanceof ThreadRepositoryInterface || !$this->beforeMove()) {
            return PodiumResponse::error();
        }

        try {
            $poll = $this->getPoll();
            if (!$poll->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'poll.not.exists')]);
            }
            if ($thread->hasPoll()) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'thread.has.poll')]);
            }

            if (!$poll->move($thread->getId())) {
                return PodiumResponse::error($thread->getErrors());
            }

            $this->afterMove();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while moving poll', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterMove(): void
    {
        $this->trigger(self::EVENT_AFTER_MOVING, new MoveEvent(['model' => $this]));
    }
}
