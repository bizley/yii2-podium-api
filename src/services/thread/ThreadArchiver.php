<?php

declare(strict_types=1);

namespace bizley\podium\api\services\thread;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ArchiveEvent;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\repositories\ThreadRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class ThreadArchiver extends Component implements ArchiverInterface
{
    public const EVENT_BEFORE_ARCHIVING = 'podium.thread.archiving.before';
    public const EVENT_AFTER_ARCHIVING = 'podium.thread.archiving.after';
    public const EVENT_BEFORE_REVIVING = 'podium.thread.reviving.before';
    public const EVENT_AFTER_REVIVING = 'podium.thread.reviving.after';

    private ?ThreadRepositoryInterface $thread = null;

    /**
     * @var string|array|ThreadRepositoryInterface
     */
    public $repositoryConfig = ThreadRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getThread(): ThreadRepositoryInterface
    {
        if (null === $this->thread) {
            /** @var ThreadRepositoryInterface $thread */
            $thread = Instance::ensure($this->repositoryConfig, ThreadRepositoryInterface::class);
            $this->thread = $thread;
        }

        return $this->thread;
    }

    public function beforeArchive(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_ARCHIVING, $event);

        return $event->canArchive;
    }

    /**
     * Archives the thread.
     */
    public function archive($id): PodiumResponse
    {
        if (!$this->beforeArchive()) {
            return PodiumResponse::error();
        }

        try {
            $thread = $this->getThread();
            if (!$thread->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'thread.not.exists')]);
            }

            if (!$thread->archive()) {
                return PodiumResponse::error($thread->getErrors());
            }

            $this->afterArchive();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while archiving thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterArchive(): void
    {
        $this->trigger(self::EVENT_AFTER_ARCHIVING, new ArchiveEvent(['model' => $this]));
    }

    public function beforeRevive(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_REVIVING, $event);

        return $event->canRevive;
    }

    /**
     * Revives the thread.
     */
    public function revive($id): PodiumResponse
    {
        if (!$this->beforeRevive()) {
            return PodiumResponse::error();
        }

        try {
            $thread = $this->getThread();
            if (!$thread->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'thread.not.exists')]);
            }

            if (!$thread->revive()) {
                return PodiumResponse::error($thread->getErrors());
            }

            $this->afterRevive();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while reviving thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterRevive(): void
    {
        $this->trigger(self::EVENT_AFTER_REVIVING, new ArchiveEvent(['model' => $this]));
    }
}
