<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

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

    /**
     * @param ArchivableInterface $postArchiver
     * @return bool
     */
    public function archive(ArchivableInterface $postArchiver): bool;

    /**
     * @param ArchivableInterface $postArchiver
     * @return bool
     */
    public function revive(ArchivableInterface $postArchiver): bool;

    /**
     * @return LikingInterface
     */
    public function getLiking(): LikingInterface;

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $post
     * @return bool
     */
    public function thumbUp(MembershipInterface $member, ModelInterface $post): bool;

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $post
     * @return bool
     */
    public function thumbDown(MembershipInterface $member, ModelInterface $post): bool;

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $post
     * @return bool
     */
    public function thumbReset(MembershipInterface $member, ModelInterface $post): bool;
}
