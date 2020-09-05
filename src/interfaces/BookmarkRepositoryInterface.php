<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface BookmarkRepositoryInterface
{
    public function fetchOne(MemberRepositoryInterface $member, ThreadRepositoryInterface $thread): bool;

    public function prepare(MemberRepositoryInterface $member, ThreadRepositoryInterface $thread): void;

    public function getErrors(): array;

    public function getLastSeen(): ?int;

    public function mark(int $timeMark): bool;
}
