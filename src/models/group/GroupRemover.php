<?php

declare(strict_types=1);

namespace bizley\podium\api\models\group;

use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\RemovableInterface;
use bizley\podium\api\repos\GroupRepo;
use Yii;

/**
 * Class GroupRemover
 * @package bizley\podium\api\models\group
 */
class GroupRemover extends GroupRepo implements RemovableInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.group.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.group.removing.after';

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

        try {
            if ($this->delete() === false) {
                Yii::error('Error while deleting group', 'podium');
                return false;
            }

            $this->afterRemove();
            return true;

        } catch (\Throwable $exc) {
            Yii::error(['Exception while removing group', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
        }
        return false;
    }

    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
