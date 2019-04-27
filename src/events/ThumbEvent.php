<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\LikerInterface;
use yii\base\Event;

/**
 * Class ThumbEvent
 * @package bizley\podium\api\events
 */
class ThumbEvent extends Event
{
    /**
     * @var bool whether member can give thumb up
     */
    public $canThumbUp = true;

    /**
     * @var bool whether member can give thumb down
     */
    public $canThumbDown = true;

    /**
     * @var bool whether member can reset thumb
     */
    public $canThumbReset = true;

    /**
     * @var LikerInterface
     */
    public $model;
}
