<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;

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
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getPosts(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface;

    /**
     * Returns post form handler instance.
     * @param int|null $id
     * @return CategorisedFormInterface|null
     */
    public function getPostForm(?int $id = null): ?CategorisedFormInterface;

    /**
     * Returns poll form handler instance.
     * @param int|null $id
     * @return CategorisedFormInterface|null
     */
    public function getPollForm(?int $id = null): ?CategorisedFormInterface;

    /**
     * Creates post.
     * @param array $data
     * @param MembershipInterface $author
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function create(array $data, MembershipInterface $author, ModelInterface $thread): PodiumResponse;

    /**
     * Updates post.
     * @param array $data
     * @return PodiumResponse
     */
    public function edit(array $data): PodiumResponse;

    /**
     * @param RemoverInterface $postRemover
     * @return PodiumResponse
     */
    public function remove(RemoverInterface $postRemover): PodiumResponse;

    /**
     * Moves post to different thread.
     * @param MovableInterface $postMover
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function move(MovableInterface $postMover, ModelInterface $thread): PodiumResponse;

    /**
     * @param ArchiverInterface $postArchiver
     * @return PodiumResponse
     */
    public function archive(ArchiverInterface $postArchiver): PodiumResponse;

    /**
     * @param ArchiverInterface $postArchiver
     * @return PodiumResponse
     */
    public function revive(ArchiverInterface $postArchiver): PodiumResponse;

    /**
     * @return LikingInterface
     */
    public function getLiking(): LikingInterface;

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function thumbUp(MembershipInterface $member, ModelInterface $post): PodiumResponse;

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function thumbDown(MembershipInterface $member, ModelInterface $post): PodiumResponse;

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function thumbReset(MembershipInterface $member, ModelInterface $post): PodiumResponse;
}
