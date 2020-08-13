<?php

declare(strict_types=1);

namespace bizley\podium\api\services\post;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\interfaces\CategoryBuilderInterface;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
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

final class PostBuilder extends Component implements CategoryBuilderInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.post.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.post.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.post.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.post.editing.after';

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

    public function beforeCreate(): bool
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_CREATING, $event);

        return $event->canCreate;
    }

    /**
     * Creates new thread.
     */
    public function create(array $data, MemberRepositoryInterface $author, RepositoryInterface $thread): PodiumResponse
    {
        if (!$thread instanceof ThreadRepositoryInterface || !$this->beforeCreate()) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = $this->getPost();

            /** @var ForumRepositoryInterface $threadParent */
            $threadParent = $thread->getParent();
            if (
                !$post->create(
                    $data,
                    $author->getId(),
                    $thread->getId(),
                    $threadParent->getId(),
                    $threadParent->getParent()->getId()
                )
            ) {
                return PodiumResponse::error($post->getErrors());
            }

            if (!$thread->updateCounters(1)) {
                throw new Exception('Error while updating thread counters!');
            }
            if (!$threadParent->updateCounters(0, 1)) {
                throw new Exception('Error while updating forum counters!');
            }

            $this->afterCreate();
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while creating post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterCreate(): void
    {
        $this->trigger(self::EVENT_AFTER_CREATING, new ModelEvent(['model' => $this]));
    }

    public function beforeEdit(): bool
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_EDITING, $event);

        return $event->canEdit;
    }

    /**
     * Edits the thread.
     */
    public function edit(int $id, array $data): PodiumResponse
    {
        if (!$this->beforeEdit()) {
            return PodiumResponse::error();
        }

        try {
            $post = $this->getPost();
            if (!$post->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'post.not.exists')]);
            }

            if (!$post->edit($data)) {
                return PodiumResponse::error($post->getErrors());
            }

            $this->afterEdit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while editing post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterEdit(): void
    {
        $this->trigger(self::EVENT_AFTER_EDITING, new ModelEvent(['model' => $this]));
    }
}
