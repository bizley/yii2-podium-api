<?php

declare(strict_types=1);

namespace bizley\podium\api\services\thread;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\BookmarkEvent;
use bizley\podium\api\interfaces\BookmarkerInterface;
use bizley\podium\api\interfaces\BookmarkRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\repositories\BookmarkRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class ThreadBookmarker extends Component implements BookmarkerInterface
{
    public const EVENT_BEFORE_MARKING = 'podium.bookmark.marking.before';
    public const EVENT_AFTER_MARKING = 'podium.bookmark.marking.after';

    private ?BookmarkRepositoryInterface $bookmark = null;

    /**
     * @var string|array|BookmarkRepositoryInterface
     */
    public $repositoryConfig = BookmarkRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getBookmark(): BookmarkRepositoryInterface
    {
        if (null === $this->bookmark) {
            /** @var BookmarkRepositoryInterface $bookmark */
            $bookmark = Instance::ensure($this->repositoryConfig, BookmarkRepositoryInterface::class);
            $this->bookmark = $bookmark;
        }

        return $this->bookmark;
    }

    public function beforeMark(): bool
    {
        $event = new BookmarkEvent();
        $this->trigger(self::EVENT_BEFORE_MARKING, $event);

        return $event->canMark;
    }

    /**
     * Bookmarks the thread.
     */
    public function mark(MemberRepositoryInterface $member, PostRepositoryInterface $post): PodiumResponse
    {
        if (!$this->beforeMark()) {
            return PodiumResponse::error();
        }

        try {
            $bookmark = $this->getBookmark();
            $memberId = $member->getId();
            $threadId = $post->getParent()->getId();
            if (!$bookmark->fetchOne($memberId, $threadId)) {
                $bookmark->prepare($memberId, $threadId);
            }

            $postCreatedTime = $post->getCreatedAt();
            if ($bookmark->getLastSeen() >= $postCreatedTime) {
                return PodiumResponse::success();
            }

            if (!$bookmark->mark($postCreatedTime)) {
                return PodiumResponse::error($bookmark->getErrors());
            }

            $this->afterMark();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while bookmarking thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterMark(): void
    {
        $this->trigger(self::EVENT_AFTER_MARKING, new BookmarkEvent(['model' => $this]));
    }
}
