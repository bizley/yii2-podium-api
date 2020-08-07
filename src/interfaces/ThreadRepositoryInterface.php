<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface ThreadRepositoryInterface extends RepositoryInterface
{
    public function isArchived(): bool;
    public function pin(): bool;
    public function unpin(): bool;
    public function move(ForumRepositoryInterface $newForum): bool;
}
