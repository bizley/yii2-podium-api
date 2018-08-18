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
     * @return ModelInterface|null
     */
    public function getCategoryById(int $id): ?ModelInterface;

    /**
     * Returns category form handler.
     * @return AuthoredFormInterface
     */
    public function getCategoryForm(): AuthoredFormInterface;

    /**
     * Creates category.
     * @param array $data
     * @param MembershipInterface $author
     * @return bool
     */
    public function create(array $data, MembershipInterface $author): bool;

    /**
     * Updates category.
     * @param ModelFormInterface $categoryForm
     * @param array $data
     * @return bool
     */
    public function edit(ModelFormInterface $categoryForm, array $data): bool;

    /**
     * @param RemovableInterface $categoryRemover
     * @return bool
     */
    public function remove(RemovableInterface $categoryRemover): bool;

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

    /**
     * @param ArchivableInterface $categoryArchiver
     * @return bool
     */
    public function archive(ArchivableInterface $categoryArchiver): bool;

    /**
     * @param ArchivableInterface $categoryArchiver
     * @return bool
     */
    public function revive(ArchivableInterface $categoryArchiver): bool;
}
