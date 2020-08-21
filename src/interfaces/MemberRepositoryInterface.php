<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface MemberRepositoryInterface extends RepositoryInterface
{
    public function ban(): bool;

    public function unban(): bool;

    public function join($groupId): bool;

    public function leave($groupId): bool;

    public function isMemberOfGroup($groupId): bool;
}
