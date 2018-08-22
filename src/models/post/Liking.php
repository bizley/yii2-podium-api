<?php

declare(strict_types=1);

namespace bizley\podium\api\models\post;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\ThumbEvent;
use bizley\podium\api\interfaces\LikingInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\repos\ThumbRepo;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;

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
        $this->setPostModel($post);

        $this->post_id = $post->getId();
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
    public function beforeThumbUp(): bool
    {
        $event = new ThumbEvent();
        $this->trigger(self::EVENT_BEFORE_THUMB_UP, $event);

        return $event->canThumbUp;
    }

    /**
     * @return PodiumResponse
     */
    public function thumbUp(): PodiumResponse
    {
        if (!$this->beforeThumbUp()) {
            return PodiumResponse::error();
        }

        $rate = static::find()->where([
            'member_id' => $this->member_id,
            'post_id' => $this->post_id,
        ])->one();

        if ($rate === null) {
            $model = $this;
        } elseif ($rate->thumb === 1) {
            $this->addError('post_id', Yii::t('podium.error', 'post.already.liked'));
            return PodiumResponse::error($this);
        } else {
            $model = $rate;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model->thumb = 1;

            if (!$model->save()) {
                Yii::error(['Error while giving thumb up', $model->errors], 'podium');
                throw new Exception('Error while giving thumb up!');
            }

            if ($rate === null) {
                if (!$this->getPostModel()->updateCounters(['likes' => 1])) {
                    throw new Exception('Error while updating post likes!');
                }
            } elseif (!$this->getPostModel()->updateCounters([
                    'likes' => 1,
                    'dislikes' => -1,
                ])) {
                throw new Exception('Error while updating post likes!');
            }

            $this->afterThumbUp();

            $transaction->commit();
            return PodiumResponse::success();

        } catch (\Throwable $exc) {
            Yii::error(['Exception while giving thumb up', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            try {
                $transaction->rollBack();
            } catch (\Throwable $excTrans) {
                Yii::error(['Exception while thumb up giving transaction rollback', $excTrans->getMessage(), $excTrans->getTraceAsString()], 'podium');
            }
        }
        return PodiumResponse::error($this);
    }

    public function afterThumbUp(): void
    {
        $this->trigger(self::EVENT_AFTER_THUMB_UP, new ThumbEvent([
            'model' => $this
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
     * @return PodiumResponse
     */
    public function thumbDown(): PodiumResponse
    {
        if (!$this->beforeThumbDown()) {
            return PodiumResponse::error();
        }

        $rate = static::find()->where([
            'member_id' => $this->member_id,
            'post_id' => $this->post_id,
        ])->one();

        if ($rate === null) {
            $model = $this;
        } elseif ($rate->thumb === -1) {
            $this->addError('post_id', Yii::t('podium.error', 'post.already.disliked'));
            return PodiumResponse::error($this);
        } else {
            $model = $rate;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model->thumb = -1;

            if (!$model->save()) {
                Yii::error(['Error while giving thumb down', $model->errors], 'podium');
                throw new Exception('Error while giving thumb down!');
            }

            if ($rate === null) {
                if (!$this->getPostModel()->updateCounters(['dislikes' => 1])) {
                    throw new Exception('Error while updating post likes!');
                }
            } elseif (!$this->getPostModel()->updateCounters([
                'likes' => -1,
                'dislikes' => 1,
            ])) {
                throw new Exception('Error while updating post likes!');
            }

            $this->afterThumbDown();

            $transaction->commit();
            return PodiumResponse::success();

        } catch (\Throwable $exc) {
            Yii::error(['Exception while giving thumb down', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            try {
                $transaction->rollBack();
            } catch (\Throwable $excTrans) {
                Yii::error(['Exception while thumb down giving transaction rollback', $excTrans->getMessage(), $excTrans->getTraceAsString()], 'podium');
            }
        }
        return PodiumResponse::error($this);
    }

    public function afterThumbDown(): void
    {
        $this->trigger(self::EVENT_AFTER_THUMB_DOWN, new ThumbEvent([
            'model' => $this
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
     * @return PodiumResponse
     */
    public function thumbReset(): PodiumResponse
    {
        if (!$this->beforeThumbReset()) {
            return PodiumResponse::error();
        }

        $rate = static::find()->where([
            'member_id' => $this->member_id,
            'post_id' => $this->post_id,
        ])->one();

        if ($rate === null) {
            $this->addError('post_id', Yii::t('podium.error', 'post.not.rated'));
            return PodiumResponse::error($this);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($rate->thumb === 1) {
                if (!$this->getPostModel()->updateCounters(['likes' => -1])) {
                    throw new Exception('Error while updating post likes!');
                }
            } elseif (!$this->getPostModel()->updateCounters(['dislikes' => -1])) {
                throw new Exception('Error while updating post likes!');
            }

            if (!$rate->delete()) {
                Yii::error(['Error while resetting thumb', $this->errors], 'podium');
                return PodiumResponse::error($this);
            }

            $this->afterThumbReset();

            $transaction->commit();
            return PodiumResponse::success();

        } catch (\Throwable $exc) {
            Yii::error(['Exception while resetting thumb', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            try {
                $transaction->rollBack();
            } catch (\Throwable $excTrans) {
                Yii::error(['Exception while thumb resetting transaction rollback', $excTrans->getMessage(), $excTrans->getTraceAsString()], 'podium');
            }
        }
        return PodiumResponse::error($this);
    }

    public function afterThumbReset(): void
    {
        $this->trigger(self::EVENT_AFTER_THUMB_RESET);
    }
}
