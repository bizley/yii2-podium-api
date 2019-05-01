<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\BookmarkEvent;
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
     * {@inheritdoc}
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
     */
    public function setMember(MembershipInterface $member): void
    {
        $this->member_id = $member->getId();
    }

    /**
     * @param ModelInterface $post
     * @throws Exception
     */
    public function setPost(ModelInterface $post): void
    {
        $this->setPostModel($post);

        $thread = $post->getParent();
        if ($thread === null) {
            throw new Exception('Can not find parent thread!');
        }

        $this->thread_id = $thread->getId();
    }

    private $_post;

    /**
     * @param ModelInterface $post
     */
    public function setPostModel(ModelInterface $post): void
    {
        $this->_post = $post;
    }

    /**
     * @return ModelInterface
     */
    public function getPostModel(): ModelInterface
    {
        return $this->_post;
    }

    /**
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
        $bookmark = static::find()->where([
            'member_id' => $this->member_id,
            'thread_id' => $this->thread_id,
        ])->one();

        return $bookmark ?? $this;
    }

    /**
     * @return PodiumResponse
     */
    public function mark(): PodiumResponse
    {
        if (!$this->beforeMark()) {
            return PodiumResponse::error();
        }

        $bookmark = $this->getBookmark();

        if ($bookmark->last_seen !== null && $bookmark->last_seen >= $this->getPostModel()->getCreatedAt()) {
            return PodiumResponse::success();
        }

        $bookmark->last_seen = $this->getPostModel()->getCreatedAt();

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
