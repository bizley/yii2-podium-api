<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\MessengerInterface;
use yii\base\Event;

/**
 * Class MessageEvent
 * @package bizley\podium\api\events
 */
class MessageEvent extends Event
{
    /**
     * @var bool whether model can be created
     */
    public bool $canSend = true;

    /**
     * @var MessengerInterface|null
     */
    public ?MessengerInterface $model = null;
}
