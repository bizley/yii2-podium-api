<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\MoveEvent;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\models\forum\Forum;
use bizley\podium\api\repos\ThreadRepo;
use Throwable;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;

/**
 * Class ThreadMover
 * @package bizley\podium\api\models\thread
 */
class ThreadMover extends ThreadRepo implements MoverInterface
{
    public const EVENT_BEFORE_MOVING = 'podium.thread.moving.before';
    public const EVENT_AFTER_MOVING = 'podium.thread.moving.after';

    /**
     * @param int $modelId
     * @return MoverInterface|null
     */
    public static function findById(int $modelId): ?MoverInterface
    {
        return static::findOne(['id' => $modelId]);
    }

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
                throw new Exception('Error while moving thread!');
            }

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

            $this->afterMove();
            $transaction->commit();

            return PodiumResponse::success();

        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while moving thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

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
