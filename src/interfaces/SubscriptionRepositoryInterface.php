<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface SubscriptionRepositoryInterface
{
    public function isMemberSubscribed(int $memberId, int $threadId): bool;
    public function subscribe(int $memberId, int $threadId): bool;
    public function fetchOne(int $memberId, int $threadId): bool;
    public function getErrors(): array;
    public function delete(): bool;
}
