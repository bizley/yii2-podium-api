<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\ars\SubscriptionActiveRecord;

interface ActiveRecordSubscriptionRepositoryInterface extends SubscriptionRepositoryInterface
{
    public function getModel(): SubscriptionActiveRecord;

    public function setModel(?SubscriptionActiveRecord $model): void;
}
