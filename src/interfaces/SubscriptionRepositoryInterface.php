<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface SubscriptionRepositoryInterface
{
    public function isMemberSubscribed(MemberRepositoryInterface $member, ThreadRepositoryInterface $thread): bool;

    public function subscribe(MemberRepositoryInterface $member, ThreadRepositoryInterface $thread): bool;

    public function fetchOne(MemberRepositoryInterface $member, ThreadRepositoryInterface $thread): bool;

    public function getErrors(): array;

    public function delete(): bool;
}
