<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

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
     * Returns forum form handler.
     * @return CategorisedFormInterface
     */
    public function getForumForm(): CategorisedFormInterface;

    /**
     * Creates forum.
     * @param array $data
     * @param MembershipInterface $author
     * @param ModelInterface $category
     * @return bool
     */
    public function create(array $data, MembershipInterface $author, ModelInterface $category): bool;

    /**
     * Updates forum.
     * @param ModelFormInterface $forumForm
     * @param array $data
     * @return bool
     */
    public function edit(ModelFormInterface $forumForm, array $data): bool;

    /**
     * @param RemovableInterface $forumRemover
     * @return bool
     */
    public function remove(RemovableInterface $forumRemover): bool;

    /**
     * @return SortableInterface
     */
    public function getForumSorter(): SortableInterface;

    /**
     * Sorts forums.
     * @param ModelInterface $category
     * @param array $data
     * @return bool
     */
    public function sort(ModelInterface $category, array $data = []): bool;

    /**
     * Moves forum to different category.
     * @param MovableInterface $forumMover
     * @param ModelInterface $category
     * @return bool
     */
    public function move(MovableInterface $forumMover, ModelInterface $category): bool;

    /**
     * @param ArchivableInterface $forumArchiver
     * @return bool
     */
    public function archive(ArchivableInterface $forumArchiver): bool;

    /**
     * @param ArchivableInterface $forumArchiver
     * @return bool
     */
    public function revive(ArchivableInterface $forumArchiver): bool;
}
