<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface BookmarkRepositoryInterface
{
    public function find(int $memberId, int $threadId): bool;
    public function create(int $memberId, int $threadId): void;
    public function getErrors(): array;
    public function getLastSeen(): ?int;
    public function mark(int $timeMark): bool;
}
