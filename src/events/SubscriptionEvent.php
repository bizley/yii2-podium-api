<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\SubscriptionRepositoryInterface;
use yii\base\Event;

class SubscriptionEvent extends Event
{
    /**
     * @var bool whether model can be subscribed
     */
    public bool $canSubscribe = true;

    /**
     * @var bool whether model can be unsubscribed
     */
    public bool $canUnsubscribe = true;

    public ?SubscriptionRepositoryInterface $repository = null;
}
