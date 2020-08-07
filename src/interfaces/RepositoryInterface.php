<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface RepositoryInterface
{
    public function getId(): int;
    public function getParent(): RepositoryInterface;
    public function find(int $id): bool;
    public function getErrors(): array;
    public function delete(): bool;
}
