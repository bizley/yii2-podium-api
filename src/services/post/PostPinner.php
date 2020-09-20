<?php

declare(strict_types=1);

namespace bizley\podium\api\services\post;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\PinEvent;
use bizley\podium\api\interfaces\PinnerInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;

final class PostPinner extends Component implements PinnerInterface
{
    public const EVENT_BEFORE_PINNING = 'podium.post.pinning.before';
    public const EVENT_AFTER_PINNING = 'podium.post.pinning.after';
    public const EVENT_BEFORE_UNPINNING = 'podium.post.unpinning.before';
    public const EVENT_AFTER_UNPINNING = 'podium.post.unpinning.after';

    /**
     * Calls before pinning the post.
     */
    public function beforePin(): bool
    {
        $event = new PinEvent();
        $this->trigger(self::EVENT_BEFORE_PINNING, $event);

        return $event->canPin;
    }

    /**
     * Pins the post.
     */
    public function pin(RepositoryInterface $post): PodiumResponse
    {
        if (!$post instanceof PostRepositoryInterface || !$this->beforePin()) {
            return PodiumResponse::error();
        }

        try {
            if (!$post->pin()) {
                return PodiumResponse::error($post->getErrors());
            }

            $this->afterPin($post);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while pinning post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after pinning the post successfully.
     */
    public function afterPin(PostRepositoryInterface $thread): void
    {
        $this->trigger(self::EVENT_AFTER_PINNING, new PinEvent(['repository' => $thread]));
    }

    /**
     * Calls before unpinning the post.
     */
    public function beforeUnpin(): bool
    {
        $event = new PinEvent();
        $this->trigger(self::EVENT_BEFORE_UNPINNING, $event);

        return $event->canUnpin;
    }

    /**
     * Unpins the post.
     */
    public function unpin(RepositoryInterface $post): PodiumResponse
    {
        if (!$post instanceof PostRepositoryInterface || !$this->beforeUnpin()) {
            return PodiumResponse::error();
        }

        try {
            if (!$post->unpin()) {
                return PodiumResponse::error($post->getErrors());
            }

            $this->afterUnpin($post);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while unpinning post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after unpinning the post successfully.
     */
    public function afterUnpin(PostRepositoryInterface $thread): void
    {
        $this->trigger(self::EVENT_AFTER_UNPINNING, new PinEvent(['repository' => $thread]));
    }
}
