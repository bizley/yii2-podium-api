<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\LockerInterface;
use yii\base\Event;

/**
 * Class LockEvent
 * @package bizley\podium\api\events
 */
class LockEvent extends Event
{
    /**
     * @var bool whether models can be locked
     */
    public bool $canLock = true;

    /**
     * @var bool whether models can be unlocked
     */
    public bool $canUnlock = true;

    /**
     * @var LockerInterface|null
     */
    public ?LockerInterface $model = null;
}
