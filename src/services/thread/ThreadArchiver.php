<?php

declare(strict_types=1);

namespace bizley\podium\api\services\thread;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ArchiveEvent;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;

final class ThreadArchiver extends Component implements ArchiverInterface
{
    public const EVENT_BEFORE_ARCHIVING = 'podium.thread.archiving.before';
    public const EVENT_AFTER_ARCHIVING = 'podium.thread.archiving.after';
    public const EVENT_BEFORE_REVIVING = 'podium.thread.reviving.before';
    public const EVENT_AFTER_REVIVING = 'podium.thread.reviving.after';

    public function beforeArchive(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_ARCHIVING, $event);

        return $event->canArchive;
    }

    /**
     * Archives the thread.
     */
    public function archive(RepositoryInterface $thread): PodiumResponse
    {
        if (!$thread instanceof ThreadRepositoryInterface || !$this->beforeArchive()) {
            return PodiumResponse::error();
        }

        try {
            if (!$thread->archive()) {
                return PodiumResponse::error($thread->getErrors());
            }

            $this->afterArchive($thread);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while archiving thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterArchive(ThreadRepositoryInterface $thread): void
    {
        $this->trigger(self::EVENT_AFTER_ARCHIVING, new ArchiveEvent(['repository' => $thread]));
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
    public function revive(RepositoryInterface $thread): PodiumResponse
    {
        if (!$thread instanceof ThreadRepositoryInterface || !$this->beforeRevive()) {
            return PodiumResponse::error();
        }

        try {
            if (!$thread->revive()) {
                return PodiumResponse::error($thread->getErrors());
            }

            $this->afterRevive($thread);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while reviving thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterRevive(ThreadRepositoryInterface $thread): void
    {
        $this->trigger(self::EVENT_AFTER_REVIVING, new ArchiveEvent(['repository' => $thread]));
    }
}
