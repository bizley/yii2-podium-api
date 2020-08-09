<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface ThreadRepositoryInterface extends RepositoryInterface
{
    public function create(array $data, int $authorId, int $forumId, int $categoryId): bool;
    public function edit(array $data): bool; // moze w RepositoryInterface?
    public function isArchived(): bool;
    public function pin(): bool;
    public function unpin(): bool;
    public function move(int $forumId, int $categoryId): bool;
    public function lock(): bool;
    public function unlock(): bool;
    public function archive(): bool;
    public function revive(): bool;
    public function getPostsCount(): int;
}
