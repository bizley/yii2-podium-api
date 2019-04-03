<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
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
     * Returns thread form handler instance.
     * @param int|null $id
     * @return CategorisedFormInterface|null
     */
    public function getForm(?int $id = null): ?CategorisedFormInterface;

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
     * @param int $id
     * @return RemoverInterface|null
     */
    public function getRemover(int $id): ?RemoverInterface;

    /**
     * @param int $id
     * @return PodiumResponse
     */
    public function remove(int $id): PodiumResponse;

    /**
     * @param int $id
     * @return MoverInterface|null
     */
    public function getMover(int $id): ?MoverInterface;

    /**
     * Moves thread to different forum.
     * @param int $id
     * @param ModelInterface $forum
     * @return PodiumResponse
     */
    public function move(int $id, ModelInterface $forum): PodiumResponse;

    /**
     * @param int $id
     * @return PinnableInterface|null
     */
    public function getPinner(int $id): ?PinnableInterface;

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
     * @param int $id
     * @return LockableInterface|null
     */
    public function getLocker(int $id): ?LockableInterface;

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
     * @return SubscribingInterface
     */
    public function getSubscriber(): SubscribingInterface;

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function subscribe(MembershipInterface $member, ModelInterface $thread): PodiumResponse;

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function unsubscribe(MembershipInterface $member, ModelInterface $thread): PodiumResponse;

    /**
     * @return BookmarkingInterface
     */
    public function getBookmarker(): BookmarkingInterface;

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function mark(MembershipInterface $member, ModelInterface $post): PodiumResponse;
}
