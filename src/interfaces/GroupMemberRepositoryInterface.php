<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface GroupMemberRepositoryInterface
{
    public function create(array $data, $memberId, $groupId): bool;
    public function exists($groupId, $memberId): bool;
    public function getErrors(): array;
    public function delete(): bool;
}
