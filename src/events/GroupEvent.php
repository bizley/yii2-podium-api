<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\GroupingInterface;
use yii\base\Event;

/**
 * Class GroupEvent
 * @package bizley\podium\api\events
 */
class GroupEvent extends Event
{
    /**
     * @var bool whether model can be joined
     */
    public $canJoin = true;

    /**
     * @var bool whether model can be left
     */
    public $canLeave = true;

    /**
     * @var GroupingInterface
     */
    public $model;
}
