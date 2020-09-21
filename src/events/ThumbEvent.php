<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\ThumbRepositoryInterface;
use yii\base\Event;

class ThumbEvent extends Event
{
    /**
     * @var bool whether member can give thumb up
     */
    public bool $canThumbUp = true;

    /**
     * @var bool whether member can give thumb down
     */
    public bool $canThumbDown = true;

    /**
     * @var bool whether member can reset thumb
     */
    public bool $canThumbReset = true;

    public ?ThumbRepositoryInterface $repository = null;
}
