<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface GroupMemberRepositoryInterface
{
    public function create($groupId, $memberId, array $data = []): bool;
    public function fetchOne($groupId, $memberId): bool;
    public function getErrors(): array;
    public function delete(): bool;
}
