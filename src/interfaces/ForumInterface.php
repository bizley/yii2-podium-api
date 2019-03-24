<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;

/**
 * Interface ForumInterface
 * @package bizley\podium\api\interfaces
 */
interface ForumInterface
{
    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getById(int $id): ?ModelInterface;

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getAll(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface;

    /**
     * Returns forum form handler instance.
     * @param int|null $id
     * @return CategorisedFormInterface|null
     */
    public function getForm(?int $id = null): ?CategorisedFormInterface;

    /**
     * Creates forum.
     * @param array $data
     * @param MembershipInterface $author
     * @param ModelInterface $category
     * @return PodiumResponse
     */
    public function create(array $data, MembershipInterface $author, ModelInterface $category): PodiumResponse;

    /**
     * Updates forum.
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
     * Sorts forums.
     * @param ModelInterface $category
     * @param array $data
     * @return PodiumResponse
     */
    public function sort(ModelInterface $category, array $data = []): PodiumResponse;

    /**
     * @param int $id
     * @return MovableInterface|null
     */
    public function getMover(int $id): ?MovableInterface;

    /**
     * Moves forum to different category.
     * @param int $id
     * @param ModelInterface $category
     * @return PodiumResponse
     */
    public function move(int $id, ModelInterface $category): PodiumResponse;

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
