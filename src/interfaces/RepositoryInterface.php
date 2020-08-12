<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface RepositoryInterface
{
    public function getId(): int;
    public function getParent(): RepositoryInterface;
    public function fetchOne(int $id): bool;
    public function fetchAll($filter = null, $sort = null, $pagination = null): void;
    public function getErrors(): array;
    public function delete(): bool;
    public function edit(array $data): bool;
}
