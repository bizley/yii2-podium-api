<?php

declare(strict_types=1);

namespace bizley\podium\api\models\post;

use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RemovableInterface;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\models\thread\ThreadRemover;
use bizley\podium\api\repos\PostRepo;
use Yii;
use yii\db\Exception;

/**
 * Class PostRemover
 * @package bizley\podium\api\models\post
 */
class PostRemover extends PostRepo implements RemovableInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.post.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.post.removing.after';

    /**
     * @return ModelInterface
     */
    public function getThreadModel(): ModelInterface
    {
        return Thread::findById($this->thread_id);
    }

    /**
     * @return bool
     */
    public function beforeRemove(): bool
    {
        $event = new RemoveEvent();
        $this->trigger(self::EVENT_BEFORE_REMOVING, $event);

        return $event->canRemove;
    }

    /**
     * @return bool
     */
    public function remove(): bool
    {
        if (!$this->beforeRemove()) {
            return false;
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($this->delete() === false) {
                Yii::error('Error while deleting post', 'podium');
                throw new Exception('Error while deleting post!');
            }

            $thread = $this->getThreadModel();

            if (!$thread->updateCounters(['posts_count' => -1])) {
                throw new Exception('Error while updating thread counters!');
            }
            if (!$thread->getParent()->updateCounters(['posts_count' => -1])) {
                throw new Exception('Error while updating forum counters!');
            }
            if ($thread->posts_count === 0 && !$thread->convert(ThreadRemover::class)->remove()) {
                throw new Exception('Error while removing empty thread!');
            }

            $this->afterRemove();

            $transaction->commit();
            return true;

        } catch (\Throwable $exc) {
            Yii::error(['Exception while removing post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            try {
                $transaction->rollBack();
            } catch (\Throwable $excTrans) {
                Yii::error(['Exception while post removing transaction rollback', $excTrans->getMessage(), $excTrans->getTraceAsString()], 'podium');
            }
        }
        return false;
    }

    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
