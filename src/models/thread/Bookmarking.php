<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\events\BookmarkEvent;
use bizley\podium\api\interfaces\BookmarkingInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\repos\BookmarkRepo;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Class Bookmarking
 * @package bizley\podium\api\models\thread
 */
class Bookmarking extends BookmarkRepo implements BookmarkingInterface
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
     */
    public function setPost(ModelInterface $post): void
    {
        $this->setPostModel($post);
        $this->thread_id = $post->getParent()->getId();
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
     * @return Bookmarking
     */
    public function getBookmark(): Bookmarking
    {
        $bookmark = static::find()->where([
            'member_id' => $this->member_id,
            'thread_id' => $this->thread_id,
        ])->one();
        return $bookmark ?? $this;
    }

    /**
     * @return bool
     */
    public function mark(): bool
    {
        if (!$this->beforeMark()) {
            return false;
        }

        $bookmark = $this->getBookmark();

        if ($bookmark->last_seen !== null && $bookmark->last_seen >= $this->getPostModel()->created_at) {
            return true;
        }
        $bookmark->last_seen = $this->getPostModel()->getCreatedAt();

        if (!$bookmark->save()) {
            Yii::error(['Error while bookmarking thread', $bookmark->errors], 'podium');
            return false;
        }
        $this->afterMark();
        return true;
    }

    public function afterMark(): void
    {
        $this->trigger(self::EVENT_AFTER_MARKING, new BookmarkEvent([
            'model' => $this
        ]));
    }
}
