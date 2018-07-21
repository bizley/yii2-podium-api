<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use yii\base\Event;

/**
 * Class RbacEvent
 * @package bizley\podium\events
 */
class RbacEvent extends Event
{
    /**
     * @var bool whether default RBAC configuration can be set
     */
    public $canSetup = true;
}
