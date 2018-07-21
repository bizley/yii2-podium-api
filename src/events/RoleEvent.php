<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use yii\base\Event;
use yii\rbac\Assignment;

/**
 * Class RoleEvent
 * @package bizley\podium\events
 */
class RoleEvent extends Event
{
    /**
     * @var bool whether role can be assigned
     */
    public $canAssign = true;

    /**
     * @var Assignment
     */
    public $assignment;
}
