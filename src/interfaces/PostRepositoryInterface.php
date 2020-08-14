<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface PostRepositoryInterface extends RepositoryInterface
{
    public function create(array $data, $authorId, $threadId, $forumId, $categoryId): bool;
    public function getCreatedAt(): int;
    public function isArchived(): bool;
    public function move($threadId, $forumId, $categoryId): bool;
    public function archive(): bool;
    public function revive(): bool;
    public function updateCounters(int $likes, int $dislikes): bool;
}
