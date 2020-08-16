<?php

declare(strict_types=1);

namespace bizley\podium\api\services\poll;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ArchiveEvent;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\PollRepositoryInterface;
use bizley\podium\api\repositories\PollRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class PollArchiver extends Component implements ArchiverInterface
{
    public const EVENT_BEFORE_ARCHIVING = 'podium.poll.archiving.before';
    public const EVENT_AFTER_ARCHIVING = 'podium.poll.archiving.after';
    public const EVENT_BEFORE_REVIVING = 'podium.poll.reviving.before';
    public const EVENT_AFTER_REVIVING = 'podium.poll.reviving.after';

    private ?PollRepositoryInterface $poll = null;

    /**
     * @var string|array|PollRepositoryInterface
     */
    public $repositoryConfig = PollRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getPost(): PollRepositoryInterface
    {
        if (null === $this->poll) {
            /** @var PollRepositoryInterface $poll */
            $poll = Instance::ensure($this->repositoryConfig, PollRepositoryInterface::class);
            $this->poll = $poll;
        }

        return $this->poll;
    }

    public function beforeArchive(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_ARCHIVING, $event);

        return $event->canArchive;
    }

    /**
     * Archives the poll.
     */
    public function archive($id): PodiumResponse
    {
        if (!$this->beforeArchive()) {
            return PodiumResponse::error();
        }

        try {
            $post = $this->getPost();
            if (!$post->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'poll.not.exists')]);
            }

            if (!$post->archive()) {
                return PodiumResponse::error($post->getErrors());
            }

            $this->afterArchive();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while archiving poll', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

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
     * Revives the poll.
     */
    public function revive($id): PodiumResponse
    {
        if (!$this->beforeRevive()) {
            return PodiumResponse::error();
        }

        try {
            $poll = $this->getPost();
            if (!$poll->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'poll.not.exists')]);
            }

            if (!$poll->revive()) {
                return PodiumResponse::error($poll->getErrors());
            }

            $this->afterRevive();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while reviving poll', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterRevive(): void
    {
        $this->trigger(self::EVENT_AFTER_REVIVING, new ArchiveEvent(['model' => $this]));
    }
}
