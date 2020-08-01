<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use yii\base\Event;

/**
 * Class RemoveEvent
 * @package bizley\podium\api\events
 */
class RemoveEvent extends Event
{
    /**
     * @var bool whether model can be removed
     */
    public bool $canRemove = true;
}
