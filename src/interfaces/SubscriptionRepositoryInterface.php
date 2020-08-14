<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface SubscriptionRepositoryInterface
{
    public function isMemberSubscribed($memberId, $threadId): bool;
    public function subscribe($memberId, $threadId): bool;
    public function fetchOne($memberId, $threadId): bool;
    public function getErrors(): array;
    public function delete(): bool;
}
