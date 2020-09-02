<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\Podium;

interface AccountInterface
{
    public function setPodium(Podium $podium): void;

    public function getPodium(): Podium;

    public function joinGroup(GroupRepositoryInterface $group): PodiumResponse;

    public function leaveGroup(GroupRepositoryInterface $group): PodiumResponse;

    public function createCategory(array $data = []): PodiumResponse;

    public function createForum(CategoryRepositoryInterface $category, array $data = []): PodiumResponse;

    public function createThread(ForumRepositoryInterface $forum, array $data = []): PodiumResponse;

    public function markPost(PostRepositoryInterface $post): PodiumResponse;
}
