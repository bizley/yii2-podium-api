<?php

declare(strict_types=1);

namespace bizley\podium\api\services\post;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ThumbEvent;
use bizley\podium\api\interfaces\LikerInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\ThumbRepositoryInterface;
use bizley\podium\api\repositories\ThumbRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\Transaction;
use yii\di\Instance;

final class PostLiker extends Component implements LikerInterface
{
    public const EVENT_BEFORE_THUMB_UP = 'podium.thumb.up.before';
    public const EVENT_AFTER_THUMB_UP = 'podium.thumb.up.after';
    public const EVENT_BEFORE_THUMB_DOWN = 'podium.thumb.down.before';
    public const EVENT_AFTER_THUMB_DOWN = 'podium.thumb.down.after';
    public const EVENT_BEFORE_THUMB_RESET = 'podium.thumb.reset.before';
    public const EVENT_AFTER_THUMB_RESET = 'podium.thumb.reset.after';

    private ?ThumbRepositoryInterface $thumb = null;

    /**
     * @var string|array|ThumbRepositoryInterface
     */
    public $repositoryConfig = ThumbRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getThumb(): ThumbRepositoryInterface
    {
        if (null === $this->thumb) {
            /** @var ThumbRepositoryInterface $thumb */
            $thumb = Instance::ensure($this->repositoryConfig, ThumbRepositoryInterface::class);
            $this->thumb = $thumb;
        }

        return $this->thumb;
    }

    /**
     * Calls before giving thumb up.
     */
    public function beforeThumbUp(): bool
    {
        $event = new ThumbEvent();
        $this->trigger(self::EVENT_BEFORE_THUMB_UP, $event);

        return $event->canThumbUp;
    }

    /**
     * Gives thumb up to the post.
     */
    public function thumbUp(PostRepositoryInterface $post, MemberRepositoryInterface $member): PodiumResponse
    {
        if (!$this->beforeThumbUp()) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $thumb = $this->getThumb();
            $rated = true;
            if (!$thumb->fetchOne($member, $post)) {
                $thumb->prepare($member, $post);
                $rated = false;
            }
            if ($thumb->isUp()) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'post.already.liked')]);
            }

            if (!$thumb->up()) {
                return PodiumResponse::error($thumb->getErrors());
            }
            if ($rated && !$post->updateCounters(1, -1)) {
                throw new Exception('Error while updating post counters!');
            }
            if (!$rated && !$post->updateCounters(1, 0)) {
                throw new Exception('Error while updating post counters!');
            }

            $this->afterThumbUp($thumb);
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while giving thumb up', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after giving thumb up successfully.
     */
    public function afterThumbUp(ThumbRepositoryInterface $thumb): void
    {
        $this->trigger(self::EVENT_AFTER_THUMB_UP, new ThumbEvent(['repository' => $thumb]));
    }

    /**
     * Calls before giving thumb down.
     */
    public function beforeThumbDown(): bool
    {
        $event = new ThumbEvent();
        $this->trigger(self::EVENT_BEFORE_THUMB_DOWN, $event);

        return $event->canThumbDown;
    }

    /**
     * Gives thumb down to the post.
     */
    public function thumbDown(PostRepositoryInterface $post, MemberRepositoryInterface $member): PodiumResponse
    {
        if (!$this->beforeThumbDown()) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $thumb = $this->getThumb();
            $rated = true;
            if (!$thumb->fetchOne($member, $post)) {
                $thumb->prepare($member, $post);
                $rated = false;
            }
            if ($thumb->isDown()) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'post.already.disliked')]);
            }

            if (!$thumb->down()) {
                return PodiumResponse::error($thumb->getErrors());
            }
            if ($rated && !$post->updateCounters(-1, 1)) {
                throw new Exception('Error while updating post counters!');
            }
            if (!$rated && !$post->updateCounters(0, 1)) {
                throw new Exception('Error while updating post counters!');
            }

            $this->afterThumbDown($thumb);
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while giving thumb down', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after giving thumb down to the post.
     */
    public function afterThumbDown(ThumbRepositoryInterface $thumb): void
    {
        $this->trigger(self::EVENT_AFTER_THUMB_DOWN, new ThumbEvent(['repository' => $thumb]));
    }

    /**
     * Calls before resetting thumb.
     */
    public function beforeThumbReset(): bool
    {
        $event = new ThumbEvent();
        $this->trigger(self::EVENT_BEFORE_THUMB_RESET, $event);

        return $event->canThumbReset;
    }

    /**
     * Resets thumb for the post.
     */
    public function thumbReset(PostRepositoryInterface $post, MemberRepositoryInterface $member): PodiumResponse
    {
        if (!$this->beforeThumbReset()) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $thumb = $this->getThumb();
            if (!$thumb->fetchOne($member, $post)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'post.not.rated')]);
            }

            $isUp = $thumb->isUp();

            if (!$thumb->reset()) {
                return PodiumResponse::error();
            }
            if ($isUp && !$post->updateCounters(-1, 0)) {
                throw new Exception('Error while updating post counters!');
            }
            if (!$isUp && !$post->updateCounters(0, -1)) {
                throw new Exception('Error while updating post counters!');
            }

            $this->afterThumbReset();
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while resetting thumb', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after resetting thumb for the post successfully.
     */
    public function afterThumbReset(): void
    {
        $this->trigger(self::EVENT_AFTER_THUMB_RESET);
    }
}
