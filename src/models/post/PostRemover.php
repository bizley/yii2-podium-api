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
        if (!$this->archived) {
            $this->addError('archived', Yii::t('podium.error', 'post.must.be.archived'));
            return false;
        }

        try {
            $thread = $this->getThreadModel();
            if ($thread->getPostsCount() === 0 && $thread->isArchived()) {
                if (!$thread->convert(ThreadRemover::class)->remove()) {
                    Yii::error('Error while deleting empty archived thread', 'podium');
                    return false;
                }

                $this->afterRemove();
                return true;
            }

            if ($this->delete() === false) {
                Yii::error('Error while deleting post', 'podium');
                return false;
            }

            $this->afterRemove();
            return true;

        } catch (\Throwable $exc) {
            Yii::error(['Exception while removing post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
        }
        return false;
    }

    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
