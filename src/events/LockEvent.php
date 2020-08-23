<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\RepositoryInterface;
use yii\base\Event;

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

    public ?RepositoryInterface $repository = null;
}
