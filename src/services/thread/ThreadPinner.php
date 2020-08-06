<?php

declare(strict_types=1);

namespace bizley\podium\api\services\thread;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\PinEvent;
use bizley\podium\api\interfaces\PinnerInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\repositories\ThreadRepository;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class ThreadPinner extends Component implements PinnerInterface
{
    public const EVENT_BEFORE_PINNING = 'podium.thread.pinning.before';
    public const EVENT_AFTER_PINNING = 'podium.thread.pinning.after';
    public const EVENT_BEFORE_UNPINNING = 'podium.thread.unpinning.before';
    public const EVENT_AFTER_UNPINNING = 'podium.thread.unpinning.after';

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

    public function beforePin(): bool
    {
        $event = new PinEvent();
        $this->trigger(self::EVENT_BEFORE_PINNING, $event);

        return $event->canPin;
    }

    /**
     * @param int $id
     * @return PodiumResponse
     * @throws InvalidConfigException
     */
    public function pin(int $id): PodiumResponse
    {
        if (!$this->beforePin()) {
            return PodiumResponse::error();
        }

        $thread = $this->getThread();
        if (!$thread->find($id)) {
            return PodiumResponse::error(['api' => Yii::t('podium.error', 'thread.not.exists')]);
        }

        if (!$thread->pin()) {
            Yii::error(['Error while pinning thread', $thread->getErrors()], 'podium');
            return PodiumResponse::error($thread->getErrors());
        }

        $this->afterPin();

        return PodiumResponse::success();
    }

    public function afterPin(): void
    {
        $this->trigger(self::EVENT_AFTER_PINNING, new PinEvent(['model' => $this]));
    }

    public function beforeUnpin(): bool
    {
        $event = new PinEvent();
        $this->trigger(self::EVENT_BEFORE_UNPINNING, $event);

        return $event->canUnpin;
    }

    /**
     * @param int $id
     * @return PodiumResponse
     * @throws InvalidConfigException
     */
    public function unpin(int $id): PodiumResponse
    {
        if (!$this->beforeUnpin()) {
            return PodiumResponse::error();
        }

        $thread = $this->getThread();
        if (!$thread->find($id)) {
            return PodiumResponse::error(['api' => Yii::t('podium.error', 'thread.not.exists')]);
        }

        if (!$thread->unpin()) {
            Yii::error(['Error while unpinning thread', $thread->getErrors()], 'podium');

            return PodiumResponse::error($thread->getErrors());
        }

        $this->afterUnpin();

        return PodiumResponse::success();
    }

    public function afterUnpin(): void
    {
        $this->trigger(self::EVENT_AFTER_UNPINNING, new PinEvent(['model' => $this]));
    }
}
