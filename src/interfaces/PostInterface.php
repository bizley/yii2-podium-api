<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface PostInterface
 * @package bizley\podium\api\interfaces
 */
interface PostInterface
{
    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getPostById(int $id): ?ModelInterface;

    /**
     * Returns post model handler.
     * @return ModelInterface
     */
    public function getPostModel(): ModelInterface;

    /**
     * Returns post form handler.
     * @return CategorisedFormInterface
     */
    public function getPostForm(): CategorisedFormInterface;

    /**
     * Creates post.
     * @param array $data
     * @param MembershipInterface $author
     * @param ModelInterface $thread
     * @return bool
     */
    public function create(array $data, MembershipInterface $author, ModelInterface $thread): bool;

    /**
     * Updates post.
     * @param ModelFormInterface $postForm
     * @param array $data
     * @return bool
     */
    public function edit(ModelFormInterface $postForm, array $data): bool;

    /**
     * @param RemovableInterface $postRemover
     * @return bool
     */
    public function remove(RemovableInterface $postRemover): bool;

    /**
     * Moves post to different thread.
     * @param MovableInterface $postMover
     * @param ModelInterface $thread
     * @return bool
     */
    public function move(MovableInterface $postMover, ModelInterface $thread): bool;
}
