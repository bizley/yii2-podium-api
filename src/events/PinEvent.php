<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\RepositoryInterface;
use yii\base\Event;

class PinEvent extends Event
{
    /**
     * @var bool whether models can be pinned
     */
    public bool $canPin = true;

    /**
     * @var bool whether models can be unpinned
     */
    public bool $canUnpin = true;

    public ?RepositoryInterface $repository = null;
}
