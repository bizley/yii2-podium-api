<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RemovableInterface;
use bizley\podium\api\models\forum\Forum;
use bizley\podium\api\repos\ThreadRepo;
use Yii;
use yii\db\Exception;

/**
 * Class ThreadRemover
 * @package bizley\podium\api\models\thread
 */
class ThreadRemover extends ThreadRepo implements RemovableInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.thread.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.thread.removing.after';

    /**
     * @return ModelInterface
     */
    public function getForumModel(): ModelInterface
    {
        return Forum::findById($this->forum_id);
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
            if (!$this->getForumModel()->updateCounters([
                'threads_count' => -1,
                'posts_count' => -$this->posts_count,
            ])) {
                throw new Exception('Error while updating forum counters!');
            }

            if ($this->delete() === false) {
                Yii::error('Error while deleting thread', 'podium');
                throw new Exception('Error while deleting thread!');
            }

            $this->afterRemove();

            $transaction->commit();
            return true;

        } catch (\Throwable $exc) {
            Yii::error(['Exception while removing thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            try {
                $transaction->rollBack();
            } catch (\Throwable $excTrans) {
                Yii::error(['Exception while thread removing transaction rollback', $excTrans->getMessage(), $excTrans->getTraceAsString()], 'podium');
            }
        }
        return false;
    }

    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
