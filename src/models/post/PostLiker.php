<?php

declare(strict_types=1);

namespace bizley\podium\api\models\post;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\ThumbEvent;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\LikerInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\repos\ThumbRepo;
use Throwable;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\db\Transaction;

/**
 * Class PostLiker
 * @package bizley\podium\api\models\post
 */
class PostLiker extends ThumbRepo implements LikerInterface
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
        return ['timestamp' => TimestampBehavior::class];
    }

    /**
     * @param MembershipInterface $member
     * @throws InsufficientDataException
     */
    public function setMember(MembershipInterface $member): void
    {
        $memberId = $member->getId();
        if ($memberId === null) {
            throw new InsufficientDataException('Missing member Id for post liker');
        }
        $this->member_id = $memberId;
    }

    /**
     * @param ModelInterface $post
     * @throws InsufficientDataException
     */
    public function setPost(ModelInterface $post): void
    {
        $this->setPostModel($post);

        $postId = $post->getId();
        if ($postId === null) {
            throw new InsufficientDataException('Missing post Id for post liker');
        }
        $this->post_id = $postId;
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

        /** @var self|null $rate */
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

        $model->thumb = 1;

        if (!$model->validate()) {
            return PodiumResponse::error($model);
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$model->save(false)) {
                throw new Exception('Error while giving thumb up!');
            }

            $post = $this->getPostModel();
            if ($post) {
                if ($rate === null) {
                    if (!$post->updateCounters(['likes' => 1])) {
                        throw new Exception('Error while updating post likes!');
                    }
                } elseif (
                    !$post->updateCounters(
                        [
                            'likes' => 1,
                            'dislikes' => -1,
                        ]
                    )
                ) {
                    throw new Exception('Error while updating post likes!');
                }
            }

            $this->afterThumbUp();
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while giving thumb up', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterThumbUp(): void
    {
        $this->trigger(self::EVENT_AFTER_THUMB_UP, new ThumbEvent(['model' => $this]));
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

        /** @var self|null $rate */
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

        $model->thumb = -1;

        if (!$model->validate()) {
            return PodiumResponse::error($model);
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$model->save(false)) {
                throw new Exception('Error while giving thumb down');
            }

            $post = $this->getPostModel();
            if ($post) {
                if ($rate === null) {
                    if (!$post->updateCounters(['dislikes' => 1])) {
                        throw new Exception('Error while updating post likes!');
                    }
                } elseif (
                    !$post->updateCounters(
                        [
                            'likes' => -1,
                            'dislikes' => 1,
                        ]
                    )
                ) {
                    throw new Exception('Error while updating post likes!');
                }
            }

            $this->afterThumbDown();
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while giving thumb down', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterThumbDown(): void
    {
        $this->trigger(self::EVENT_AFTER_THUMB_DOWN, new ThumbEvent(['model' => $this]));
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

        /** @var self|null $rate */
        $rate = static::find()->where([
            'member_id' => $this->member_id,
            'post_id' => $this->post_id,
        ])->one();

        if ($rate === null) {
            $this->addError('post_id', Yii::t('podium.error', 'post.not.rated'));

            return PodiumResponse::error($this);
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = $this->getPostModel();
            if ($post) {
                if ($rate->thumb === 1) {
                    if (!$post->updateCounters(['likes' => -1])) {
                        throw new Exception('Error while updating post likes!');
                    }
                } elseif (!$post->updateCounters(['dislikes' => -1])) {
                    throw new Exception('Error while updating post likes!');
                }
            }

            if ($rate->delete() === false) {
                throw new Exception('Error while resetting thumb');
            }

            $this->afterThumbReset();
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while resetting thumb', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterThumbReset(): void
    {
        $this->trigger(self::EVENT_AFTER_THUMB_RESET);
    }
}
