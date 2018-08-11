<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

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
     * Returns forum model handler.
     * @return ModelInterface
     */
    public function getForumModel(): ModelInterface;

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
}
