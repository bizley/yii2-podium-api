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
    public function edit($id, array $data): PodiumResponse;

    public function remove($id): PodiumResponse;

    /**
     * Replaces the order of the forums.
     */
    public function replace($id, ForumRepositoryInterface $forum): PodiumResponse;

    /**
     * Moves forum to different category.
     */
    public function move($id, CategoryRepositoryInterface $category): PodiumResponse;

    public function archive($id): PodiumResponse;

    public function revive($id): PodiumResponse;
}
