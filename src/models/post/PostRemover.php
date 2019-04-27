<?php

declare(strict_types=1);

namespace bizley\podium\api\models\post;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\models\thread\ThreadRemover;
use bizley\podium\api\repos\PostRepo;
use Yii;
use yii\db\Exception;

/**
 * Class PostRemover
 * @package bizley\podium\api\models\post
 */
class PostRemover extends PostRepo implements RemoverInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.post.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.post.removing.after';

    /**
     * @param int $modelId
     * @return RemoverInterface|null
     */
    public static function findById(int $modelId): ?RemoverInterface
    {
        return static::findOne(['id' => $modelId]);
    }

    /**
     * @return ModelInterface|null
     */
    public function getThreadModel(): ?ModelInterface
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
     * @return PodiumResponse
     */
    public function remove(): PodiumResponse
    {
        if (!$this->beforeRemove()) {
            return PodiumResponse::error();
        }

        if (!$this->archived) {
            $this->addError('archived', Yii::t('podium.error', 'post.must.be.archived'));
            return PodiumResponse::error($this);
        }

        try {
            $thread = $this->getThreadModel();
            if ($thread === null) {
                throw new Exception('Can not find parent thread!');
            }
            if ($thread->getPostsCount() === 0 && $thread->isArchived()) {
                if (!$thread->convert(ThreadRemover::class)->remove()->result) {
                    Yii::error('Error while deleting empty archived thread', 'podium');
                    return PodiumResponse::error();
                }

                $this->afterRemove();

                return PodiumResponse::success();
            }

            if ($this->delete() === false) {
                Yii::error('Error while deleting post', 'podium');
                return PodiumResponse::error();
            }

            $this->afterRemove();

            return PodiumResponse::success();

        } catch (\Throwable $exc) {
            Yii::error(['Exception while removing post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            return PodiumResponse::error();
        }
    }

    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
