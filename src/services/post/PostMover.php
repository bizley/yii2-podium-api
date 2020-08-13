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
use bizley\podium\api\repositories\PostRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\Transaction;
use yii\di\Instance;

final class PostMover extends Component implements MoverInterface
{
    public const EVENT_BEFORE_MOVING = 'podium.post.moving.before';
    public const EVENT_AFTER_MOVING = 'podium.post.moving.after';

    private ?PostRepositoryInterface $post = null;

    /**
     * @var string|array|PostRepositoryInterface
     */
    public $repositoryConfig = PostRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getPost(): PostRepositoryInterface
    {
        if (null === $this->post) {
            /** @var PostRepositoryInterface $post */
            $post = Instance::ensure($this->repositoryConfig, PostRepositoryInterface::class);
            $this->post = $post;
        }

        return $this->post;
    }

    public function beforeMove(): bool
    {
        $event = new MoveEvent();
        $this->trigger(self::EVENT_BEFORE_MOVING, $event);

        return $event->canMove;
    }

    /**
     * Moves the thread to another forum.
     */
    public function move(int $id, RepositoryInterface $thread): PodiumResponse
    {
        if (!$thread instanceof ThreadRepositoryInterface || !$this->beforeMove()) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = $this->getPost();
            if (!$post->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'post.not.exists')]);
            }

            /** @var ForumRepositoryInterface $threadParent */
            $threadParent = $thread->getParent();
            if (!$post->move($thread->getId(), $threadParent->getId(), $threadParent->getParent()->getId())) {
                return PodiumResponse::error($thread->getErrors());
            }

            /** @var ThreadRepositoryInterface $postParent */
            $postParent = $post->getParent();
            if (!$postParent->updateCounters(-1)) {
                throw new Exception('Error while updating old thread counters!');
            }
            /** @var ForumRepositoryInterface $postGrandParent */
            $postGrandParent = $postParent->getParent();
            if (!$postGrandParent->updateCounters(0, -1)) {
                throw new Exception('Error while updating old thread counters!');
            }
            if (!$thread->updateCounters(1)) {
                throw new Exception('Error while updating new forum counters!');
            }
            if (!$threadParent->updateCounters(0, 1)) {
                throw new Exception('Error while updating new forum counters!');
            }

            $transaction->commit();
            $this->afterMove();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while moving post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterMove(): void
    {
        $this->trigger(self::EVENT_AFTER_MOVING, new MoveEvent(['model' => $this]));
    }
}
