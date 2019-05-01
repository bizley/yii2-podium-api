<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\PinnerInterface;
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
    public $canLock = true;

    /**
     * @var bool whether models can be unlocked
     */
    public $canUnlock = true;

    /**
     * @var PinnerInterface
     */
    public $model;
}
