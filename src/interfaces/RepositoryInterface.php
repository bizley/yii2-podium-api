<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface RepositoryInterface
{
    public function getId();
    public function getParent(): RepositoryInterface;
    public function fetchOne($id): bool;
    public function fetchAll($filter = null, $sort = null, $pagination = null): void;
    public function getErrors(): array;
    public function delete(): bool;
    public function edit(array $data): bool;
}
