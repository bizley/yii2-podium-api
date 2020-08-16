<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\MoverInterface;
use yii\base\Event;

class MoveEvent extends Event
{
    /**
     * @var bool whether model can be moved
     */
    public bool $canMove = true;

    public ?MoverInterface $model = null;
}
