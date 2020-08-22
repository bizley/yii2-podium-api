<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface CategoryInterface
{
    /**
     * Creates category.
     */
    public function create(MemberRepositoryInterface $author, array $data = []): PodiumResponse;

    /**
     * Updates category.
     */
    public function edit(CategoryRepositoryInterface $category, array $data = []): PodiumResponse;

    public function remove(CategoryRepositoryInterface $category): PodiumResponse;

    /**
     * Replaces the order of the categories.
     */
    public function replace(
        CategoryRepositoryInterface $firstCategory,
        CategoryRepositoryInterface $secondCategory
    ): PodiumResponse;

    public function sort(): PodiumResponse;

    public function archive(CategoryRepositoryInterface $category): PodiumResponse;

    public function revive(CategoryRepositoryInterface $category): PodiumResponse;
}
