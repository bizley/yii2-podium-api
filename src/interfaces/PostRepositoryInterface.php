<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface PostRepositoryInterface extends RepositoryInterface
{
    public function create(array $data, int $authorId, int $threadId, int $forumId, int $categoryId): bool;
    public function getCreatedAt(): int;
    public function isArchived(): bool;
    public function move(int $threadId, int $forumId, int $categoryId): bool;
    public function archive(): bool;
    public function revive(): bool;
    public function updateCounters(int $likes, int $dislikes): bool;
}
