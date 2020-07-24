<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\BookmarkEvent;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\BookmarkerInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\repos\BookmarkRepo;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;

/**
 * Class ThreadBookmarker
 * @package bizley\podium\api\models\thread
 */
class ThreadBookmarker extends BookmarkRepo implements BookmarkerInterface
{
    public const EVENT_BEFORE_MARKING = 'podium.bookmark.marking.before';
    public const EVENT_AFTER_MARKING = 'podium.bookmark.marking.after';

    /**
     * Adds TimestampBehavior.
     * @return array<string|int, mixed>
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => false,
            ],
        ];
    }

    /**
     * @param MembershipInterface $member
     * @throws InsufficientDataException
     */
    public function setMember(MembershipInterface $member): void
    {
        $memberId = $member->getId();
        if ($memberId === null) {
            throw new InsufficientDataException('Missing member Id for thread bookmarker');
        }
        $this->member_id = $memberId;
    }

    /**
     * @param ModelInterface $post
     * @throws Exception
     * @throws InsufficientDataException
     */
    public function setPost(ModelInterface $post): void
    {
        $this->setPostModel($post);

        $thread = $post->getParent();
        if ($thread === null) {
            throw new Exception('Can not find parent thread!');
        }

        $threadId = $thread->getId();
        if ($threadId === null) {
            throw new InsufficientDataException('Missing post parent Id for thread bookmarker');
        }
        $this->thread_id = $threadId;
    }

    private ?ModelInterface $post = null;

    /**
     * @param ModelInterface $post
     */
    public function setPostModel(ModelInterface $post): void
    {
        $this->post = $post;
    }

    /**
     * @return ModelInterface|null
     */
    public function getPostModel(): ?ModelInterface
    {
        return $this->post;
    }

    /**
     * Executes before mark().
     * @return bool
     */
    public function beforeMark(): bool
    {
        $event = new BookmarkEvent();
        $this->trigger(self::EVENT_BEFORE_MARKING, $event);

        return $event->canMark;
    }

    /**
     * @return ThreadBookmarker
     */
    public function getBookmark(): ThreadBookmarker
    {
        /** @var ThreadBookmarker|null $bookmark */
        $bookmark = static::find()->where(
            [
                'member_id' => $this->member_id,
                'thread_id' => $this->thread_id,
            ]
        )->one();
        return $bookmark ?? $this;
    }

    /**
     * Bookmarks the thread.
     * @return PodiumResponse
     */
    public function mark(): PodiumResponse
    {
        if (!$this->beforeMark()) {
            return PodiumResponse::error();
        }

        $bookmark = $this->getBookmark();
        $post = $this->getPostModel();

        if ($post) {
            if ($bookmark->last_seen !== null && $bookmark->last_seen >= $post->getCreatedAt()) {
                return PodiumResponse::success();
            }

            $bookmark->last_seen = $post->getCreatedAt();
        }

        if (!$bookmark->save()) {
            Yii::error(['Error while bookmarking thread', $bookmark->errors], 'podium');

            return PodiumResponse::error($bookmark);
        }

        $this->afterMark();

        return PodiumResponse::success();
    }

    public function afterMark(): void
    {
        $this->trigger(self::EVENT_AFTER_MARKING, new BookmarkEvent(['model' => $this]));
    }
}
