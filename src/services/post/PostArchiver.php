<?php

declare(strict_types=1);

namespace bizley\podium\api\services\post;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ArchiveEvent;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\repositories\PostRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class PostArchiver extends Component implements ArchiverInterface
{
    public const EVENT_BEFORE_ARCHIVING = 'podium.post.archiving.before';
    public const EVENT_AFTER_ARCHIVING = 'podium.post.archiving.after';
    public const EVENT_BEFORE_REVIVING = 'podium.post.reviving.before';
    public const EVENT_AFTER_REVIVING = 'podium.post.reviving.after';

    private ?PostRepositoryInterface $post = null;

    /**
     * @var string|array|PostRepositoryInterface
     */
    public $repositoryConfig = PostRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getPost(): PostRepositoryInterface
    {
        if (null === $this->post) {
            /** @var PostRepositoryInterface $post */
            $post = Instance::ensure($this->repositoryConfig, PostRepositoryInterface::class);
            $this->post = $post;
        }

        return $this->post;
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
    public function archive(int $id): PodiumResponse
    {
        if (!$this->beforeArchive()) {
            return PodiumResponse::error();
        }

        try {
            $post = $this->getPost();
            if (!$post->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'post.not.exists')]);
            }

            if (!$post->archive()) {
                return PodiumResponse::error($post->getErrors());
            }

            $this->afterArchive();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while archiving post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

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
    public function revive(int $id): PodiumResponse
    {
        if (!$this->beforeRevive()) {
            return PodiumResponse::error();
        }

        try {
            $thread = $this->getPost();
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
