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
    public function getForumById(int $id): ?ModelInterface;

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getForums(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface;

    /**
     * Returns forum form handler instance.
     * @param int|null $id
     * @return CategorisedFormInterface|null
     */
    public function getForumForm(?int $id = null): ?CategorisedFormInterface;

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
     * @param RemovableInterface $forumRemover
     * @return PodiumResponse
     */
    public function remove(RemovableInterface $forumRemover): PodiumResponse;

    /**
     * @return SortableInterface
     */
    public function getForumSorter(): SortableInterface;

    /**
     * Sorts forums.
     * @param ModelInterface $category
     * @param array $data
     * @return PodiumResponse
     */
    public function sort(ModelInterface $category, array $data = []): PodiumResponse;

    /**
     * Moves forum to different category.
     * @param MovableInterface $forumMover
     * @param ModelInterface $category
     * @return PodiumResponse
     */
    public function move(MovableInterface $forumMover, ModelInterface $category): PodiumResponse;

    /**
     * @param ArchivableInterface $forumArchiver
     * @return PodiumResponse
     */
    public function archive(ArchivableInterface $forumArchiver): PodiumResponse;

    /**
     * @param ArchivableInterface $forumArchiver
     * @return PodiumResponse
     */
    public function revive(ArchivableInterface $forumArchiver): PodiumResponse;
}
