<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface PollInterface
 * @package bizley\podium\api\interfaces
 */
interface PollInterface
{
    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getPollByPostId(int $id): ?ModelInterface;

    /**
     * Returns poll form handler.
     * @return CategorisedFormInterface
     */
    public function getPollForm(): CategorisedFormInterface;

    /**
     * Creates poll post.
     * @param array $data
     * @param MembershipInterface $author
     * @param ModelInterface $thread
     * @return bool
     */
    public function create(array $data, MembershipInterface $author, ModelInterface $thread): bool;

    /**
     * Updates poll post.
     * @param ModelFormInterface $postPollForm
     * @param array $data
     * @return bool
     */
    public function edit(ModelFormInterface $postPollForm, array $data): bool;

    /**
     * @param RemovableInterface $pollRemover
     * @return bool
     */
    public function remove(RemovableInterface $pollRemover): bool;
}
