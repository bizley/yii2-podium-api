<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\RemovableInterface;
use bizley\podium\api\repos\ThreadRepo;
use Yii;

/**
 * Class ThreadRemover
 * @package bizley\podium\api\models\thread
 */
class ThreadRemover extends ThreadRepo implements RemovableInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.thread.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.thread.removing.after';

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
            $this->addError('archived', Yii::t('podium.error', 'thread.must.be.archived'));
            return false;
        }

        try {
            if ($this->delete() === false) {
                Yii::error('Error while deleting thread', 'podium');
                return false;
            }

            $this->afterRemove();
            return true;

        } catch (\Throwable $exc) {
            Yii::error(['Exception while deleting thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
        }
        return false;
    }

    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
