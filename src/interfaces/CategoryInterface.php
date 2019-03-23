<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;

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
     * @param DataFilter|null $filter
     * @param bool|array|Sort|null $sort
     * @param bool|array|Pagination|null $pagination
     * @return DataProviderInterface
     */
    public function getCategories(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface;

    /**
     * Returns category form handler instance.
     * @param int|null $id
     * @return AuthoredFormInterface|null
     */
    public function getCategoryForm(?int $id = null): ?AuthoredFormInterface;

    /**
     * Creates category.
     * @param array $data
     * @param MembershipInterface $author
     * @return PodiumResponse
     */
    public function create(array $data, MembershipInterface $author): PodiumResponse;

    /**
     * Updates category.
     * @param array $data
     * @return PodiumResponse
     */
    public function edit(array $data): PodiumResponse;

    /**
     * @param RemovableInterface $categoryRemover
     * @return PodiumResponse
     */
    public function remove(RemovableInterface $categoryRemover): PodiumResponse;

    /**
     * @return SortableInterface
     */
    public function getCategorySorter(): SortableInterface;

    /**
     * Sorts categories.
     * @param array $data
     * @return PodiumResponse
     */
    public function sort(array $data = []): PodiumResponse;

    /**
     * @param ArchivableInterface $categoryArchiver
     * @return PodiumResponse
     */
    public function archive(ArchivableInterface $categoryArchiver): PodiumResponse;

    /**
     * @param ArchivableInterface $categoryArchiver
     * @return PodiumResponse
     */
    public function revive(ArchivableInterface $categoryArchiver): PodiumResponse;
}
