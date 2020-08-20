<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\AcquaintanceInterface;
use bizley\podium\api\interfaces\IgnorerInterface;
use yii\base\Event;

class AcquaintanceEvent extends Event
{
    /**
     * @var bool whether member and target can be friends
     */
    public bool $canBeFriends = true;

    /**
     * @var bool whether member can unfriend target
     */
    public bool $canUnfriend = true;

    /**
     * @var bool whether member can ignore target
     */
    public bool $canIgnore = true;

    /**
     * @var bool whether member can unignore target
     */
    public bool $canUnignore = true;

    /**
     * @var AcquaintanceInterface|IgnorerInterface|null
     */
    public $model;
}
