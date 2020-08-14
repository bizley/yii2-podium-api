<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface BookmarkRepositoryInterface
{
    public function fetchOne($memberId, $threadId): bool;
    public function prepare($memberId, $threadId): void;
    public function getErrors(): array;
    public function getLastSeen(): ?int;
    public function mark(int $timeMark): bool;
}
