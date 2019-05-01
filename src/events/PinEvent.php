<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\PinnerInterface;
use yii\base\Event;

/**
 * Class PinEvent
 * @package bizley\podium\api\events
 */
class PinEvent extends Event
{
    /**
     * @var bool whether models can be pinned
     */
    public $canPin = true;

    /**
     * @var bool whether models can be unpinned
     */
    public $canUnpin = true;

    /**
     * @var PinnerInterface
     */
    public $model;
}
