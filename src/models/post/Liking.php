<?php

declare(strict_types=1);

namespace bizley\podium\api\models\post;

use bizley\podium\api\events\ThumbEvent;
use bizley\podium\api\interfaces\LikingInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\repos\ThumbRepo;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Class Liking
 * @package bizley\podium\api\models\post
 */
class Liking extends ThumbRepo implements LikingInterface
{
    public const EVENT_BEFORE_THUMB_UP = 'podium.thumb.up.before';
    public const EVENT_AFTER_THUMB_UP = 'podium.thumb.up.after';
    public const EVENT_BEFORE_THUMB_DOWN = 'podium.thumb.down.before';
    public const EVENT_AFTER_THUMB_DOWN = 'podium.thumb.down.after';
    public const EVENT_BEFORE_THUMB_RESET = 'podium.thumb.reset.before';
    public const EVENT_AFTER_THUMB_RESET = 'podium.thumb.reset.after';

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => TimestampBehavior::class,
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
        $this->post_id = $post->getId();
    }

    /**
     * @return bool
     */
    public function beforeThumbUp(): bool
    {
        $event = new ThumbEvent();
        $this->trigger(self::EVENT_BEFORE_THUMB_UP, $event);

        return $event->canThumbUp;
    }

    /**
     * @return bool
     */
    public function thumbUp(): bool
    {
        if (!$this->beforeThumbUp()) {
            return false;
        }
        if (static::find()->where([
                'member_id' => $this->member_id,
                'post_id' => $this->post_id,
                'thumb' => 1,
            ])->exists()) {
            $this->addError('post_id', Yii::t('podium.error', 'post.already.liked'));
            return false;
        }

        $this->thumb = 1;

        if (!$this->save()) {
            Yii::error(['Error while giving thumb up', $this->errors], 'podium');
            return false;
        }
        $this->afterThumbUp();
        return true;
    }

    public function afterThumbUp(): void
    {
        $this->trigger(self::EVENT_AFTER_THUMB_UP, new ThumbEvent([
            'thumb' => $this
        ]));
    }

    /**
     * @return bool
     */
    public function beforeThumbDown(): bool
    {
        $event = new ThumbEvent();
        $this->trigger(self::EVENT_BEFORE_THUMB_DOWN, $event);

        return $event->canThumbDown;
    }

    /**
     * @return bool
     */
    public function thumbDown(): bool
    {
        if (!$this->beforeThumbDown()) {
            return false;
        }
        if (static::find()->where([
            'member_id' => $this->member_id,
            'post_id' => $this->post_id,
            'thumb' => -1,
        ])->exists()) {
            $this->addError('post_id', Yii::t('podium.error', 'post.already.disliked'));
            return false;
        }

        $this->thumb = -1;

        if (!$this->save()) {
            Yii::error(['Error while giving thumb down', $this->errors], 'podium');
            return false;
        }
        $this->afterThumbDown();
        return true;
    }

    public function afterThumbDown(): void
    {
        $this->trigger(self::EVENT_AFTER_THUMB_DOWN, new ThumbEvent([
            'thumb' => $this
        ]));
    }

    /**
     * @return bool
     */
    public function beforeThumbReset(): bool
    {
        $event = new ThumbEvent();
        $this->trigger(self::EVENT_BEFORE_THUMB_RESET, $event);

        return $event->canThumbReset;
    }

    /**
     * @return bool
     */
    public function thumbReset(): bool
    {
        if (!$this->beforeThumbReset()) {
            return false;
        }

        $rate = static::find()->where([
            'member_id' => $this->member_id,
            'post_id' => $this->post_id,
        ])->one();

        if ($rate === null) {
            $this->addError('post_id', Yii::t('podium.error', 'post.not.rated'));
            return false;
        }

        try {
            if (!$rate->delete()) {
                Yii::error(['Error while resetting thumb', $this->errors], 'podium');
                return false;
            }
        } catch (\Throwable $exc) {
            Yii::error(['Exception while resetting thumb', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            return false;
        }

        $this->afterThumbReset();
        return true;
    }

    public function afterThumbReset(): void
    {
        $this->trigger(self::EVENT_AFTER_THUMB_RESET);
    }
}
