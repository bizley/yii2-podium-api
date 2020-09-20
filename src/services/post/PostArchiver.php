<?php

declare(strict_types=1);

namespace bizley\podium\api\services\post;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ArchiveEvent;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;

final class PostArchiver extends Component implements ArchiverInterface
{
    public const EVENT_BEFORE_ARCHIVING = 'podium.post.archiving.before';
    public const EVENT_AFTER_ARCHIVING = 'podium.post.archiving.after';
    public const EVENT_BEFORE_REVIVING = 'podium.post.reviving.before';
    public const EVENT_AFTER_REVIVING = 'podium.post.reviving.after';

    /**
     * Calls before archiving the post.
     */
    public function beforeArchive(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_ARCHIVING, $event);

        return $event->canArchive;
    }

    /**
     * Archives the post.
     */
    public function archive(RepositoryInterface $post): PodiumResponse
    {
        if (!$post instanceof PostRepositoryInterface || !$this->beforeArchive()) {
            return PodiumResponse::error();
        }

        try {
            if (!$post->archive()) {
                return PodiumResponse::error($post->getErrors());
            }

            $this->afterArchive($post);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while archiving post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after archiving post successfully.
     */
    public function afterArchive(PostRepositoryInterface $post): void
    {
        $this->trigger(self::EVENT_AFTER_ARCHIVING, new ArchiveEvent(['repository' => $post]));
    }

    /**
     * Calls before reviving the post.
     */
    public function beforeRevive(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_REVIVING, $event);

        return $event->canRevive;
    }

    /**
     * Revives the post.
     */
    public function revive(RepositoryInterface $post): PodiumResponse
    {
        if (!$post instanceof PostRepositoryInterface || !$this->beforeRevive()) {
            return PodiumResponse::error();
        }

        try {
            if (!$post->revive()) {
                return PodiumResponse::error($post->getErrors());
            }

            $this->afterRevive($post);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while reviving post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after reviving the post successfully.
     */
    public function afterRevive(PostRepositoryInterface $post): void
    {
        $this->trigger(self::EVENT_AFTER_REVIVING, new ArchiveEvent(['repository' => $post]));
    }
}
