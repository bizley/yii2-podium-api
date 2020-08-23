<?php

declare(strict_types=1);

namespace bizley\podium\api\services\poll;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ArchiveEvent;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\PollRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;

final class PollArchiver extends Component implements ArchiverInterface
{
    public const EVENT_BEFORE_ARCHIVING = 'podium.poll.archiving.before';
    public const EVENT_AFTER_ARCHIVING = 'podium.poll.archiving.after';
    public const EVENT_BEFORE_REVIVING = 'podium.poll.reviving.before';
    public const EVENT_AFTER_REVIVING = 'podium.poll.reviving.after';

    public function beforeArchive(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_ARCHIVING, $event);

        return $event->canArchive;
    }

    /**
     * Archives the poll.
     */
    public function archive(RepositoryInterface $poll): PodiumResponse
    {
        if (!$poll instanceof PollRepositoryInterface || !$this->beforeArchive()) {
            return PodiumResponse::error();
        }

        try {
            if (!$poll->archive()) {
                return PodiumResponse::error($poll->getErrors());
            }

            $this->afterArchive($poll);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while archiving poll', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterArchive(PollRepositoryInterface $poll): void
    {
        $this->trigger(self::EVENT_AFTER_ARCHIVING, new ArchiveEvent(['repository' => $poll]));
    }

    public function beforeRevive(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_REVIVING, $event);

        return $event->canRevive;
    }

    /**
     * Revives the poll.
     */
    public function revive(RepositoryInterface $poll): PodiumResponse
    {
        if (!$poll instanceof PollRepositoryInterface || !$this->beforeRevive()) {
            return PodiumResponse::error();
        }

        try {
            if (!$poll->revive()) {
                return PodiumResponse::error($poll->getErrors());
            }

            $this->afterRevive($poll);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while reviving poll', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterRevive(PollRepositoryInterface $poll): void
    {
        $this->trigger(self::EVENT_AFTER_REVIVING, new ArchiveEvent(['repository' => $poll]));
    }
}
