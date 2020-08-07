<?php

declare(strict_types=1);

namespace bizley\podium\api\services\thread;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\MoveEvent;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\repositories\ThreadRepository;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class ThreadMover extends Component implements MoverInterface
{
    public const EVENT_BEFORE_MOVING = 'podium.thread.moving.before';
    public const EVENT_AFTER_MOVING = 'podium.thread.moving.after';

    private ?ThreadRepositoryInterface $thread = null;

    /**
     * @var string|array|ThreadRepositoryInterface
     */
    public $repositoryConfig = ThreadRepository::class;

    /**
     * @return ThreadRepositoryInterface
     * @throws InvalidConfigException
     */
    private function getThread(): ThreadRepositoryInterface
    {
        if ($this->thread === null) {
            /** @var ThreadRepositoryInterface $thread */
            $thread = Instance::ensure($this->repositoryConfig, ThreadRepositoryInterface::class);
            $this->thread = $thread;
        }
        return $this->thread;
    }

    /**
     * Executes before move().
     * @return bool
     */
    public function beforeMove(): bool
    {
        $event = new MoveEvent();
        $this->trigger(self::EVENT_BEFORE_MOVING, $event);

        return $event->canMove;
    }

    /**
     * Moves the thread to another forum.
     * @param int $id
     * @param RepositoryInterface $forum
     * @return PodiumResponse
     * @throws InvalidConfigException
     */
    public function move(int $id, RepositoryInterface $forum): PodiumResponse
    {
        if (!$forum instanceof ForumRepositoryInterface || !$this->beforeMove()) {
            return PodiumResponse::error();
        }

        $thread = $this->getThread();
        if (!$thread->find($id)) {
            return PodiumResponse::error(['api' => Yii::t('podium.error', 'thread.not.exists')]);
        }

        if (!$thread->move($forum)) {
            Yii::error(['Error while moving thread', $thread->getErrors()], 'podium');
            return PodiumResponse::error($thread->getErrors());
        }

        $this->afterMove();
        return PodiumResponse::success();
    }

    /**
     * Executes after successful move().
     */
    public function afterMove(): void
    {
        $this->trigger(self::EVENT_AFTER_MOVING, new MoveEvent(['model' => $this]));
    }
}
