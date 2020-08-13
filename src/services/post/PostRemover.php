<?php

declare(strict_types=1);

namespace bizley\podium\api\services\post;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\repositories\PostRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\Transaction;
use yii\di\Instance;

final class PostRemover extends Component implements RemoverInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.post.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.post.removing.after';

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

    public function beforeRemove(): bool
    {
        $event = new RemoveEvent();
        $this->trigger(self::EVENT_BEFORE_REMOVING, $event);

        return $event->canRemove;
    }

    /**
     * Removes the thread.
     */
    public function remove(int $id): PodiumResponse
    {
        if (!$this->beforeRemove()) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = $this->getPost();
            if (!$post->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'post.not.exists')]);
            }
            if (!$post->isArchived()) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'post.must.be.archived')]);
            }

            if (!$post->delete()) {
                return PodiumResponse::error();
            }

            /** @var ThreadRepositoryInterface $thread */
            $thread = $post->getParent();
            if (!$thread->updateCounters(-1)) {
                throw new Exception('Error while updating thread counters!');
            }

            /** @var ForumRepositoryInterface $forum */
            $forum = $thread->getParent();
            if (!$forum->updateCounters(0, -1)) {
                throw new Exception('Error while updating forum counters!');
            }

            $transaction->commit();
            $this->afterRemove();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while deleting post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
