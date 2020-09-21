<?php

declare(strict_types=1);

namespace bizley\podium\api\services\post;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\MoveEvent;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;
use yii\db\Exception;
use yii\db\Transaction;

final class PostMover extends Component implements MoverInterface
{
    public const EVENT_BEFORE_MOVING = 'podium.post.moving.before';
    public const EVENT_AFTER_MOVING = 'podium.post.moving.after';

    /**
     * Calls before moving the post.
     */
    public function beforeMove(): bool
    {
        $event = new MoveEvent();
        $this->trigger(self::EVENT_BEFORE_MOVING, $event);

        return $event->canMove;
    }

    /**
     * Moves the post to another thread.
     */
    public function move(RepositoryInterface $post, RepositoryInterface $thread): PodiumResponse
    {
        if (
            !$post instanceof PostRepositoryInterface
            || !$thread instanceof ThreadRepositoryInterface
            || !$this->beforeMove()
        ) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            /** @var ForumRepositoryInterface $threadParent */
            $threadParent = $thread->getParent();
            if (!$post->move($thread)) {
                return PodiumResponse::error($post->getErrors());
            }

            /** @var ThreadRepositoryInterface $postParent */
            $postParent = $post->getParent();
            if (!$postParent->updateCounters(-1)) {
                throw new Exception('Error while updating old thread counters!');
            }
            /** @var ForumRepositoryInterface $postGrandParent */
            $postGrandParent = $postParent->getParent();
            if (!$postGrandParent->updateCounters(0, -1)) {
                throw new Exception('Error while updating old forum counters!');
            }
            if (!$thread->updateCounters(1)) {
                throw new Exception('Error while updating new thread counters!');
            }
            if (!$threadParent->updateCounters(0, 1)) {
                throw new Exception('Error while updating new forum counters!');
            }

            $this->afterMove($post);
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while moving post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after moving the post successfully.
     */
    public function afterMove(PostRepositoryInterface $post): void
    {
        $this->trigger(self::EVENT_AFTER_MOVING, new MoveEvent(['repository' => $post]));
    }
}
