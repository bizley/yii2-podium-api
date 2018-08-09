<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\events\MoveEvent;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MovableInterface;
use bizley\podium\api\models\forum\Forum;
use bizley\podium\api\repos\ThreadRepo;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;

/**
 * Class ThreadMover
 * @package bizley\podium\api\models\thread
 */
class ThreadMover extends ThreadRepo implements MovableInterface
{
    public const EVENT_BEFORE_MOVING = 'podium.thread.moving.before';
    public const EVENT_AFTER_MOVING = 'podium.thread.moving.after';

    /**
     * @param ModelInterface $forum
     */
    public function setForum(ModelInterface $forum): void
    {
        $this->fetchOldForumModel();
        $this->setNewForumModel($forum);

        $this->forum_id = $forum->getId();
        $this->category_id = $forum->getParent()->getId();
    }

    private $_newForum;

    /**
     * @param ModelInterface $forum
     */
    public function setNewForumModel(ModelInterface $forum): void
    {
        $this->_newForum = $forum;
    }

    /**
     * @return ModelInterface
     */
    public function getNewForumModel(): ModelInterface
    {
        return $this->_newForum;
    }

    private $_oldForum;

    public function fetchOldForumModel(): void
    {
        $this->_oldForum = Forum::findById($this->forum_id);
    }

    /**
     * @return ModelInterface
     */
    public function getOldForumModel(): ModelInterface
    {
        return $this->_oldForum;
    }

    /**
     * @return array
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => TimestampBehavior::class,
        ];
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
     * @return bool
     */
    public function move(): bool
    {
        if (!$this->beforeMove()) {
            return false;
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->getOldForumModel()->updateCounters([
                'threads_count' => -1,
                'posts_count' => -$this->posts_count,
            ])) {
                throw new Exception('Error while updating old forum counters!');
            }

            if (!$this->getNewForumModel()->updateCounters([
                'threads_count' => 1,
                'posts_count' => $this->posts_count,
            ])) {
                throw new Exception('Error while updating new forum counters!');
            }

            if (!$this->save(false)) {
                Yii::error(['thread.move', $this->errors], 'podium');
                throw new Exception('Error while moving thread!');
            }

            $this->afterMove();

            $transaction->commit();
            return true;

        } catch (\Throwable $exc) {
            Yii::error(['thread.move.exception', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            try {
                $transaction->rollBack();
            } catch (\Throwable $excTrans) {
                Yii::error(['thread.move.rollback', $excTrans->getMessage(), $excTrans->getTraceAsString()], 'podium');
            }
        }
        return false;
    }

    public function afterMove(): void
    {
        $this->trigger(self::EVENT_AFTER_MOVING, new MoveEvent([
            'model' => $this
        ]));
    }

    /**
     * @param ModelInterface $category
     * @throws NotSupportedException
     */
    public function setCategory(ModelInterface $category): void
    {
        throw new NotSupportedException('Thread target category can not be set directly.');
    }

    /**
     * @param ModelInterface $thread
     * @throws NotSupportedException
     */
    public function setThread(ModelInterface $thread): void
    {
        throw new NotSupportedException('Thread can not be moved to a Thread.');
    }
}
