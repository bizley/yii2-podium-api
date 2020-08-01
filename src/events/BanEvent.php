<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\BanisherInterface;
use yii\base\Event;

/**
 * Class ModelEvent
 * @package bizley\podium\api\events
 */
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

    /**
     * @var BanisherInterface|null
     */
    public ?BanisherInterface $model = null;
}
