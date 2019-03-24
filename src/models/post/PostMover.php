<?php

declare(strict_types=1);

namespace bizley\podium\api\models\post;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\MoveEvent;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MovableInterface;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\models\thread\ThreadArchiver;
use bizley\podium\api\models\thread\ThreadRemover;
use bizley\podium\api\repos\PostRepo;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;

/**
 * Class PostMover
 * @package bizley\podium\api\models\post
 */
class PostMover extends PostRepo implements MovableInterface
{
    public const EVENT_BEFORE_MOVING = 'podium.post.moving.before';
    public const EVENT_AFTER_MOVING = 'podium.post.moving.after';

    /**
     * @param int $modelId
     * @return MovableInterface|null
     */
    public static function findById(int $modelId): ?MovableInterface
    {
        return static::findOne(['id' => $modelId]);
    }

    /**
     * @param ModelInterface $thread
     */
    public function setThread(ModelInterface $thread): void
    {
        $this->fetchOldThreadModel();
        $this->setNewThreadModel($thread);

        $this->thread_id = $thread->getId();
        $forum = $thread->getParent();
        $this->forum_id = $forum->getId();
        $this->category_id = $forum->getParent()->getId();
    }

    private $_newThread;

    /**
     * @param ModelInterface $thread
     */
    public function setNewThreadModel(ModelInterface $thread): void
    {
        $this->_newThread = $thread;
    }

    /**
     * @return ModelInterface
     */
    public function getNewThreadModel(): ModelInterface
    {
        return $this->_newThread;
    }

    private $_oldThread;

    public function fetchOldThreadModel(): void
    {
        $this->_oldThread = Thread::findById($this->thread_id);
    }

    /**
     * @return ModelInterface
     */
    public function getOldThreadModel(): ModelInterface
    {
        return $this->_oldThread;
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

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->save(false)) {
                throw new Exception('Error while moving post!');
            }

            if (!$this->getOldThreadModel()->updateCounters(['posts_count' => -1])) {
                throw new Exception('Error while updating old thread counters!');
            }
            if (!$this->getOldThreadModel()->getParent()->updateCounters(['posts_count' => -1])) {
                throw new Exception('Error while updating old forum counters!');
            }
            if ($this->getOldThreadModel()->getPostsCount() === 0) {
                $threadArchiver = $this->getOldThreadModel()->convert(ThreadArchiver::class);
                if (!$threadArchiver->archive()) {
                    throw new Exception('Error while archiving old empty thread!');
                }
                $threadRemover = $this->getOldThreadModel()->convert(ThreadRemover::class);
                $threadRemover->archived = true;
                if (!$threadRemover->remove()->result) {
                    throw new Exception('Error while deleting old empty thread!');
                }
            }

            if (!$this->getNewThreadModel()->updateCounters(['posts_count' => 1])) {
                throw new Exception('Error while updating new thread counters!');
            }
            if (!$this->getNewThreadModel()->getParent()->updateCounters(['posts_count' => 1])) {
                throw new Exception('Error while updating new forum counters!');
            }

            $this->afterMove();

            $transaction->commit();

            return PodiumResponse::success();

        } catch (\Throwable $exc) {
            Yii::error(['Exception while moving post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            try {
                $transaction->rollBack();
            } catch (\Throwable $excTrans) {
                Yii::error(['Exception while post moving transaction rollback', $excTrans->getMessage(), $excTrans->getTraceAsString()], 'podium');
            }
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
    public function setCategory(ModelInterface $category): void
    {
        throw new NotSupportedException('Post target category can not be set directly.');
    }

    /**
     * @param ModelInterface $forum
     * @throws NotSupportedException
     */
    public function setForum(ModelInterface $forum): void
    {
        throw new NotSupportedException('Post target forum can not be set directly.');
    }
}
