<?php

declare(strict_types=1);

namespace bizley\podium\api\models\post;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\MoveEvent;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\models\thread\ThreadArchiver;
use bizley\podium\api\models\thread\ThreadRemover;
use Throwable;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\db\Transaction;

/**
 * Class PostMover
 * @package bizley\podium\api\models\post
 */
class PostMover extends Post implements MoverInterface
{
    public const EVENT_BEFORE_MOVING = 'podium.post.moving.before';
    public const EVENT_AFTER_MOVING = 'podium.post.moving.after';

    /**
     * @param ModelInterface $thread
     * @throws Exception
     * @throws InsufficientDataException
     */
    public function prepareThread(ModelInterface $thread): void
    {
        $this->fetchOldThreadModel();
        $this->setNewThreadModel($thread);

        $threadId = $thread->getId();
        if ($threadId === null) {
            throw new InsufficientDataException('Missing thread Id for post mover');
        }
        $this->thread_id = $threadId;

        $forum = $thread->getParent();
        if ($forum === null) {
            throw new Exception('Can not find parent forum!');
        }
        $forumId = $forum->getId();
        if ($forumId === null) {
            throw new InsufficientDataException('Missing thread parent Id for post mover');
        }
        $this->forum_id = $forumId;

        $category = $forum->getParent();
        if ($category === null) {
            throw new Exception('Can not find parent category!');
        }
        $categoryId = $category->getId();
        if ($categoryId === null) {
            throw new InsufficientDataException('Missing thread grandparent Id for post mover');
        }
        $this->category_id = $categoryId;
    }

    private ?ModelInterface $newThread = null;

    /**
     * @param ModelInterface $thread
     */
    public function setNewThreadModel(ModelInterface $thread): void
    {
        $this->newThread = $thread;
    }

    /**
     * @return ModelInterface|null
     */
    public function getNewThreadModel(): ?ModelInterface
    {
        return $this->newThread;
    }

    private ?ModelInterface $oldThread = null;

    public function fetchOldThreadModel(): void
    {
        $this->oldThread = Thread::findById($this->thread_id);
    }

    /**
     * @return ModelInterface|null
     */
    public function getOldThreadModel(): ?ModelInterface
    {
        return $this->oldThread;
    }

    /**
     * @return array
     */
    public function behaviors(): array
    {
        return ['timestamp' => TimestampBehavior::class];
    }

    /**
     * @return bool
     */
    public function beforeMove(): bool
    {
        $event = new MoveEvent();
        $this->trigger(self::EVENT_BEFORE_MOVING, $event);

        return $event->canMove;
    }

    /**
     * @return PodiumResponse
     */
    public function move(): PodiumResponse
    {
        if (!$this->beforeMove()) {
            return PodiumResponse::error();
        }

        if (!$this->validate()) {
            return PodiumResponse::error($this);
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->save(false)) {
                throw new Exception('Error while moving post!');
            }

            $oldThread = $this->getOldThreadModel();
            if ($oldThread) {
                if (!$oldThread->updateCounters(['posts_count' => -1])) {
                    throw new Exception('Error while updating old thread counters!');
                }

                $oldForum = $oldThread->getParent();
                if ($oldForum === null) {
                    throw new Exception('Can not find old parent forum!');
                }
                if (!$oldForum->updateCounters(['posts_count' => -1])) {
                    throw new Exception('Error while updating old forum counters!');
                }
                if ($oldThread->getPostsCount() === 0) {
                    $threadArchiver = $oldThread->convert(ThreadArchiver::class);
                    if (!$threadArchiver->archive()) {
                        throw new Exception('Error while archiving old empty thread!');
                    }

                    $threadRemover = $oldThread->convert(ThreadRemover::class);
                    $threadRemover->archived = true;
                    if (!$threadRemover->remove()->result) {
                        throw new Exception('Error while deleting old empty thread!');
                    }
                }
            }

            $newThread = $this->getNewThreadModel();
            if ($newThread) {
                if (!$newThread->updateCounters(['posts_count' => 1])) {
                    throw new Exception('Error while updating new thread counters!');
                }

                $newForum = $newThread->getParent();
                if ($newForum === null) {
                    throw new Exception('Can not find new parent forum!');
                }
                if (!$newForum->updateCounters(['posts_count' => 1])) {
                    throw new Exception('Error while updating new forum counters!');
                }
            }

            $this->afterMove();
            $transaction->commit();

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

    /**
     * @param ModelInterface $category
     * @throws NotSupportedException
     */
    public function prepareCategory(ModelInterface $category): void
    {
        throw new NotSupportedException('Post target category can not be set directly.');
    }

    /**
     * @param ModelInterface $forum
     * @throws NotSupportedException
     */
    public function prepareForum(ModelInterface $forum): void
    {
        throw new NotSupportedException('Post target forum can not be set directly.');
    }
}
