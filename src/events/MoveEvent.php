<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\MovableInterface;
use yii\base\Event;

/**
 * Class MoveEvent
 * @package bizley\podium\api\events
 */
class MoveEvent extends Event
{
    /**
     * @var bool whether models can be sorted
     */
    public $canMove = true;

    /**
     * @var MovableInterface
     */
    public $model;
}
