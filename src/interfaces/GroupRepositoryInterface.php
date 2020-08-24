<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface GroupRepositoryInterface extends RepositoryInterface
{
    public function create(array $data = []): bool;
}
