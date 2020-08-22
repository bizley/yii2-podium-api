<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface CategoryRepositoryInterface extends RepositoryInterface
{
    public function create($authorId, array $data = []): bool;
    public function isArchived(): bool;
    public function archive(): bool;
    public function revive(): bool;
    public function setOrder(int $order): bool;
    public function getOrder(): int;
    public function sort(): bool;
}
