<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\MoveEvent;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\models\forum\Forum;
use Throwable;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\db\Transaction;

/**
 * Class ThreadMover
 * @package bizley\podium\api\models\thread
 */
class ThreadMover extends Thread implements MoverInterface
{
    public const EVENT_BEFORE_MOVING = 'podium.thread.moving.before';
    public const EVENT_AFTER_MOVING = 'podium.thread.moving.after';

    /**
     * Prepares the target forum.
     * @param ModelInterface $forum
     * @throws Exception
     * @throws InsufficientDataException
     */
    public function prepareForum(ModelInterface $forum): void
    {
        $this->fetchOldForumModel();
        $this->setNewForumModel($forum);

        $forumId = $forum->getId();
        if ($forumId === null) {
            throw new InsufficientDataException('Missing forum Id for thread mover.');
        }
        $this->forum_id = $forumId;

        $category = $forum->getParent();
        if ($category === null) {
            throw new Exception('Can not find parent category!');
        }
        $categoryId = $category->getId();
        if ($categoryId === null) {
            throw new InsufficientDataException('Missing forum parent Id for thread mover.');
        }
        $this->category_id = $categoryId;
    }

    private ?ModelInterface $newForum = null;

    /**
     * @param ModelInterface $forum
     */
    public function setNewForumModel(ModelInterface $forum): void
    {
        $this->newForum = $forum;
    }

    /**
     * @return ModelInterface|null
     */
    public function getNewForumModel(): ?ModelInterface
    {
        return $this->newForum;
    }

    private ?ModelInterface $oldForum = null;

    public function fetchOldForumModel(): void
    {
        $this->oldForum = Forum::findById($this->forum_id);
    }

    /**
     * @return ModelInterface|null
     */
    public function getOldForumModel(): ?ModelInterface
    {
        return $this->oldForum;
    }

    /**
     * Adds TimestampBehavior.
     * @return array<string|int, mixed>
     */
    public function behaviors(): array
    {
        return ['timestamp' => TimestampBehavior::class];
    }

    /**
     * Executes before move().
     * @return bool
     */
    public function beforeMove(): bool
    {
        $event = new MoveEvent();
        $this->trigger(self::EVENT_BEFORE_MOVING, $event);

        return $event->canMove;
    }

    /**
     * Moves the thread to another forum.
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
                throw new Exception('Error while moving thread!');
            }

            if (
                ($oldForum = $this->getOldForumModel())
                && !$oldForum->updateCounters(
                    [
                        'threads_count' => -1,
                        'posts_count' => -$this->posts_count,
                    ]
                )
            ) {
                throw new Exception('Error while updating old forum counters!');
            }

            if (
                ($newForum = $this->getNewForumModel())
                && !$newForum->updateCounters(
                    [
                        'threads_count' => 1,
                        'posts_count' => $this->posts_count,
                    ]
                )
            ) {
                throw new Exception('Error while updating new forum counters!');
            }

            $this->afterMove();
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while moving thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    /**
     * Executes after successful move().
     */
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
        throw new NotSupportedException('Thread target category can not be set directly.');
    }

    /**
     * @param ModelInterface $thread
     * @throws NotSupportedException
     */
    public function prepareThread(ModelInterface $thread): void
    {
        throw new NotSupportedException('Thread can not be moved to a Thread.');
    }
}
