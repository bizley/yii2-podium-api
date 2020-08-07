<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface SubscriptionRepositoryInterface extends RepositoryInterface
{
    public function isMemberSubscribed(int $memberId, int $threadId): bool;
    public function subscribe(int $memberId, int $threadId): bool;

}
