<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface ThreadInterface
 * @package bizley\podium\api\interfaces
 */
interface ThreadInterface
{
    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getThreadById(int $id): ?ModelInterface;

    /**
     * Returns thread form handler.
     * @return CategorisedFormInterface
     */
    public function getThreadForm(): CategorisedFormInterface;

    /**
     * Creates thread.
     * @param array $data
     * @param MembershipInterface $author
     * @param ModelInterface $forum
     * @return bool
     */
    public function create(array $data, MembershipInterface $author, ModelInterface $forum): bool;

    /**
     * Updates thread.
     * @param ModelFormInterface $threadForm
     * @param array $data
     * @return bool
     */
    public function edit(ModelFormInterface $threadForm, array $data): bool;

    /**
     * @param RemovableInterface $threadRemover
     * @return bool
     */
    public function remove(RemovableInterface $threadRemover): bool;

    /**
     * Moves thread to different forum.
     * @param MovableInterface $threadMover
     * @param ModelInterface $forum
     * @return bool
     */
    public function move(MovableInterface $threadMover, ModelInterface $forum): bool;

    /**
     * @param PinnableInterface $threadPinner
     * @return bool
     */
    public function pin(PinnableInterface $threadPinner): bool;

    /**
     * @param PinnableInterface $threadPinner
     * @return bool
     */
    public function unpin(PinnableInterface $threadPinner): bool;

    /**
     * @param LockableInterface $threadLocker
     * @return bool
     */
    public function lock(LockableInterface $threadLocker): bool;

    /**
     * @param LockableInterface $threadLocker
     * @return bool
     */
    public function unlock(LockableInterface $threadLocker): bool;

    /**
     * @param ArchivableInterface $threadArchiver
     * @return bool
     */
    public function archive(ArchivableInterface $threadArchiver): bool;

    /**
     * @param ArchivableInterface $threadArchiver
     * @return bool
     */
    public function revive(ArchivableInterface $threadArchiver): bool;

    /**
     * @return SubscribingInterface
     */
    public function getSubscribing(): SubscribingInterface;

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $thread
     * @return bool
     */
    public function subscribe(MembershipInterface $member, ModelInterface $thread): bool;

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $thread
     * @return bool
     */
    public function unsubscribe(MembershipInterface $member, ModelInterface $thread): bool;

    /**
    * @return BookmarkingInterface
    */
    public function getBookmarking(): BookmarkingInterface;

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $post
     * @return bool
     */
    public function mark(MembershipInterface $member, ModelInterface $post): bool;
}
