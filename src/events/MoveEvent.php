<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\MoverInterface;
use yii\base\Event;

/**
 * Class MoveEvent
 * @package bizley\podium\api\events
 */
class MoveEvent extends Event
{
    /**
     * @var bool whether model can be moved
     */
    public $canMove = true;

    /**
     * @var MoverInterface
     */
    public $model;
}
