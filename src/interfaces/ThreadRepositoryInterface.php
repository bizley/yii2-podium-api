<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface ThreadRepositoryInterface
{
    public function find(int $id): bool;
    public function isArchived(): bool;
    public function delete(): bool;
    public function pin(): bool;
    public function unpin(): bool;
    public function getErrors(): array;
}
