<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface ForumInterface
{
    /**
     * Creates forum.
     */
    public function create(
        array $data,
        MemberRepositoryInterface $author,
        CategoryRepositoryInterface $category
    ): PodiumResponse;

    /**
     * Updates forum.
     */
    public function edit(array $data): PodiumResponse;

    public function remove(int $id): PodiumResponse;

    /**
     * Replaces the order of the forums.
     */
    public function replace($id, ForumRepositoryInterface $forum): PodiumResponse;

    /**
     * Moves forum to different category.
     */
    public function move(int $id, CategoryRepositoryInterface $category): PodiumResponse;

    public function archive(int $id): PodiumResponse;

    public function revive(int $id): PodiumResponse;
}
