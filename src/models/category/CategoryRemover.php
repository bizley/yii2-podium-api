<?php

declare(strict_types=1);

namespace bizley\podium\api\models\category;

use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\RemovableInterface;
use bizley\podium\api\repos\CategoryRepo;
use Yii;

/**
 * Class ForumRemover
 * @package bizley\podium\api\models\category
 */
class CategoryRemover extends CategoryRepo implements RemovableInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.category.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.category.removing.after';

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
                Yii::error('Error while deleting category', 'podium');
                return false;
            }

            $this->afterRemove();
            return true;

        } catch (\Throwable $exc) {
            Yii::error(['Exception while removing category', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
        }
        return false;
    }

    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
