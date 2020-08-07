<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface ForumRepositoryInterface extends RepositoryInterface
{
    public function updateCounters(int $threads, int $posts): bool;
}
