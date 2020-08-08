<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;
use yii\data\DataFilter;
use yii\data\Pagination;
use yii\data\Sort;

/**
 * Interface ThreadInterface
 * @package bizley\podium\api\interfaces
 */
interface ThreadInterface
{
    /**
     * @param int $id
     * @return ThreadRepositoryInterface
     */
    public function getById(int $id): ThreadRepositoryInterface;

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return ThreadRepositoryInterface
     */
    public function getAll($filter = null, $sort = null, $pagination = null): ThreadRepositoryInterface;

    /**
     * Returns thread form handler instance.
     * @param int|null $id
     * @return CategorisedFormInterface|null
     */
    public function getForm(int $id = null): ?CategorisedFormInterface;

    /**
     * Creates thread.
     * @param array $data
     * @param MembershipInterface $author
     * @param ModelInterface $forum
     * @return PodiumResponse
     */
    public function create(array $data, MembershipInterface $author, ModelInterface $forum): PodiumResponse;

    /**
     * Updates thread.
     * @param array $data
     * @return PodiumResponse
     */
    public function edit(array $data): PodiumResponse;

    /**
     * @return RemoverInterface
     */
    public function getRemover(): RemoverInterface;

    /**
     * @param int $id
     * @return PodiumResponse
     */
    public function remove(int $id): PodiumResponse;

    /**
     * @return MoverInterface|null
     */
    public function getMover(): ?MoverInterface;

    /**
     * Moves thread to different forum.
     * @param int $id
     * @param ForumRepositoryInterface $forum
     * @return PodiumResponse
     */
    public function move(int $id, ForumRepositoryInterface $forum): PodiumResponse;

    /**
     * @return PinnerInterface
     */
    public function getPinner(): PinnerInterface;

    /**
     * @param int $id
     * @return PodiumResponse
     */
    public function pin(int $id): PodiumResponse;

    /**
     * @param int $id
     * @return PodiumResponse
     */
    public function unpin(int $id): PodiumResponse;

    /**
     * @return LockerInterface
     */
    public function getLocker(): LockerInterface;

    /**
     * @param int $id
     * @return PodiumResponse
     */
    public function lock(int $id): PodiumResponse;

    /**
     * @param int $id
     * @return PodiumResponse
     */
    public function unlock(int $id): PodiumResponse;

    /**
     * @return ArchiverInterface
     */
    public function getArchiver(): ArchiverInterface;

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

    /**
     * @return SubscriberInterface
     */
    public function getSubscriber(): SubscriberInterface;

    /**
     * @param MembershipInterface $member
     * @param ThreadRepositoryInterface $thread
     * @return PodiumResponse
     */
    public function subscribe(MembershipInterface $member, ThreadRepositoryInterface $thread): PodiumResponse;

    /**
     * @param MembershipInterface $member
     * @param ThreadRepositoryInterface $thread
     * @return PodiumResponse
     */
    public function unsubscribe(MembershipInterface $member, ThreadRepositoryInterface $thread): PodiumResponse;

    /**
     * @return BookmarkerInterface
     */
    public function getBookmarker(): BookmarkerInterface;

    /**
     * @param MembershipInterface $member
     * @param PostRepositoryInterface $post
     * @return PodiumResponse
     */
    public function mark(MembershipInterface $member, PostRepositoryInterface $post): PodiumResponse;
}
