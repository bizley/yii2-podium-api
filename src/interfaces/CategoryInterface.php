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
    public function getById(int $id): ?ModelInterface;

    /**
     * @param DataFilter|null $filter
     * @param bool|array|Sort|null $sort
     * @param bool|array|Pagination|null $pagination
     * @return DataProviderInterface
     */
    public function getAll(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface;

    /**
     * Returns category form handler instance.
     * @param int|null $id
     * @return AuthoredFormInterface|null
     */
    public function getForm(?int $id = null): ?AuthoredFormInterface;

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
     * @param int $id
     * @return RemovableInterface|null
     */
    public function getRemover(int $id): ?RemovableInterface;

    /**
     * @param int $id
     * @return PodiumResponse
     */
    public function remove(int $id): PodiumResponse;

    /**
     * @return SortableInterface
     */
    public function getSorter(): SortableInterface;

    /**
     * Sorts categories.
     * @param array $data
     * @return PodiumResponse
     */
    public function sort(array $data = []): PodiumResponse;

    /**
     * @param int $id
     * @return ArchivableInterface|null
     */
    public function getArchiver(int $id): ?ArchivableInterface;

    /**
     * @param int $id
     * @return PodiumResponse
     */
    public function archive(int $id): PodiumResponse;

    /**
     * @param int $id
     * @return PodiumResponse
     */
    public function revive(int $id): PodiumResponse;
}
