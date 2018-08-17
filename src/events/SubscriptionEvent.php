<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\SubscribingInterface;
use yii\base\Event;

/**
 * Class SubscriptionEvent
 * @package bizley\podium\api\events
 */
class SubscriptionEvent extends Event
{
    /**
     * @var bool whether model can be subscribed
     */
    public $canSubscribe = true;

    /**
     * @var bool whether model can be unsubscribed
     */
    public $canUnsubscribe = true;

    /**
     * @var SubscribingInterface
     */
    public $model;
}
