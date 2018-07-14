<?php

declare(strict_types=1);

namespace bizley\podium\events;

use bizley\podium\api\repos\AcquaintanceRepo;
use yii\base\Event;

/**
 * Class AcquaintanceEvent
 * @package bizley\podium\events
 */
class AcquaintanceEvent extends Event
{
    /**
     * @var bool whether member and target can be friends
     */
    public $canBeFriends = true;

    /**
     * @var bool whether member can unfriend target
     */
    public $canUnfriend = true;

    /**
     * @var bool whether member can ignore target
     */
    public $canIgnore = true;

    /**
     * @var bool whether member can unignore target
     */
    public $canUnignore = true;

    /**
     * @var AcquaintanceRepo
     */
    public $acquaintance;
}
