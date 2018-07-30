<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface CategoryInterface
 * @package bizley\podium\api\interfaces
 */
interface CategoryInterface
{
    /**
     * @param int $id
     * @return CategoryInterface|null
     */
    public function getCategoryById(int $id): ?ModelInterface;

    /**
     * Returns category model handler.
     * @return ModelInterface
     */
    public function getCategoryModel(): ModelInterface;

    /**
     * Returns category form handler.
     * @return CategoryFormInterface
     */
    public function getCategoryForm(): CategoryFormInterface;

    /**
     * Creates category.
     * @param array $data
     * @param MembershipInterface $author
     * @return bool
     */
    public function create(array $data, MembershipInterface $author): bool;

    /**
     * Updates category.
     * @param CategoryFormInterface $categoryForm
     * @param array $data
     * @return bool
     */
    public function edit(CategoryFormInterface $categoryForm, array $data): bool;

    /**
     * @return SortableInterface
     */
    public function getCategorySorter(): SortableInterface;

    /**
     * Sorts categories.
     * @param array $data
     * @return bool
     */
    public function sort(array $data = []): bool;
}
