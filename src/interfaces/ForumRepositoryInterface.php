<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface ForumRepositoryInterface extends RepositoryInterface
{
    public function create(array $data, $authorId, $categoryId): bool;
    public function move($categoryId): bool;
    public function isArchived(): bool;
    public function archive(): bool;
    public function revive(): bool;
    public function updateCounters(int $threads, int $posts): bool;
    public function setOrder(int $order): bool;
    public function getOrder(): int;
}
