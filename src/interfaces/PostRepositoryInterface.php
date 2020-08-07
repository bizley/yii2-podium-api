<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface PostRepositoryInterface extends RepositoryInterface
{
    public function getCreatedAt(): int;
}
