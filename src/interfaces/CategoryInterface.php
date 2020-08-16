<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface CategoryInterface
{
    /**
     * Creates category.
     */
    public function create(array $data, MemberRepositoryInterface $author): PodiumResponse;

    /**
     * Updates category.
     */
    public function edit($id, array $data): PodiumResponse;

    public function remove(int $id): PodiumResponse;

    /**
     * Replaces the order of the categories.
     */
    public function replace($id, CategoryRepositoryInterface $category): PodiumResponse;

    public function archive(int $id): PodiumResponse;

    public function revive(int $id): PodiumResponse;
}
