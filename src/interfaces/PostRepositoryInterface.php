<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface PostRepositoryInterface extends RepositoryInterface
{
    public function create(
        MemberRepositoryInterface $author,
        ThreadRepositoryInterface $thread,
        array $data = []
    ): bool;

    public function getCreatedAt(): int;

    public function isArchived(): bool;

    public function move(ThreadRepositoryInterface $thread): bool;

    public function archive(): bool;

    public function revive(): bool;

    public function updateCounters(int $likes, int $dislikes): bool;

    public function pin(): bool;

    public function unpin(): bool;
}
