<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\RepositoryInterface;
use yii\base\Event;

class BanEvent extends Event
{
    /**
     * @var bool whether member can be banned
     */
    public bool $canBan = true;

    /**
     * @var bool whether member can be unbanned
     */
    public bool $canUnban = true;

    public ?RepositoryInterface $repository = null;
}
