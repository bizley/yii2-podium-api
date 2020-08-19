<?php

declare(strict_types=1);

namespace bizley\podium\api\services\forum;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ArchiveEvent;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\repositories\ForumRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class ForumArchiver extends Component implements ArchiverInterface
{
    public const EVENT_BEFORE_ARCHIVING = 'podium.forum.archiving.before';
    public const EVENT_AFTER_ARCHIVING = 'podium.forum.archiving.after';
    public const EVENT_BEFORE_REVIVING = 'podium.forum.reviving.before';
    public const EVENT_AFTER_REVIVING = 'podium.forum.reviving.after';

    private ?ForumRepositoryInterface $forum = null;

    /**
     * @var string|array|ForumRepositoryInterface
     */
    public $repositoryConfig = ForumRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getForum(): ForumRepositoryInterface
    {
        if (null === $this->forum) {
            /** @var ForumRepositoryInterface $forum */
            $forum = Instance::ensure($this->repositoryConfig, ForumRepositoryInterface::class);
            $this->forum = $forum;
        }

        return $this->forum;
    }

    public function beforeArchive(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_ARCHIVING, $event);

        return $event->canArchive;
    }

    /**
     * Archives the forum.
     */
    public function archive($id): PodiumResponse
    {
        if (!$this->beforeArchive()) {
            return PodiumResponse::error();
        }

        try {
            $forum = $this->getForum();
            if (!$forum->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'forum.not.exists')]);
            }

            if (!$forum->archive()) {
                return PodiumResponse::error($forum->getErrors());
            }

            $this->afterArchive();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while archiving forum', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

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
     * Revives the forum.
     */
    public function revive($id): PodiumResponse
    {
        if (!$this->beforeRevive()) {
            return PodiumResponse::error();
        }

        try {
            $forum = $this->getForum();
            if (!$forum->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'forum.not.exists')]);
            }

            if (!$forum->revive()) {
                return PodiumResponse::error($forum->getErrors());
            }

            $this->afterRevive();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while reviving forum', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterRevive(): void
    {
        $this->trigger(self::EVENT_AFTER_REVIVING, new ArchiveEvent(['model' => $this]));
    }
}
