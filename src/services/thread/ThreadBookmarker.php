<?php

declare(strict_types=1);

namespace bizley\podium\api\services\thread;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\BookmarkEvent;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\BookmarkerInterface;
use bizley\podium\api\interfaces\BookmarkRepositoryInterface;
use bizley\podium\api\interfaces\MembershipInterface;
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
     * @return BookmarkRepositoryInterface
     * @throws InvalidConfigException
     */
    private function getBookmark(): BookmarkRepositoryInterface
    {
        if ($this->bookmark === null) {
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
     * @param PostRepositoryInterface $post
     * @param MembershipInterface $member
     * @return PodiumResponse
     * @throws InsufficientDataException
     * @throws InvalidConfigException
     */
    public function mark(PostRepositoryInterface $post, MembershipInterface $member): PodiumResponse
    {
        if (!$this->beforeMark()) {
            return PodiumResponse::error();
        }

        $bookmark = $this->getBookmark();
        $memberId = $member->getId();
        if ($memberId === null) {
            throw new InsufficientDataException('Missing member ID for Thread Bookmarker!');
        }
        $threadId = $post->getParent()->getId();
        if (!$bookmark->find($memberId, $threadId)) {
            $bookmark->create($memberId, $threadId);
        }

        $postCreatedTime = $post->getCreatedAt();
        if ($bookmark->getLastSeen() >= $postCreatedTime) {
            return PodiumResponse::success();
        }

        try {
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
