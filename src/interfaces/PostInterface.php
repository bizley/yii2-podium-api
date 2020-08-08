<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;
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
    public function getById(int $id): ?ModelInterface;

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getAll(DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface;

    /**
     * Returns post form handler instance.
     * @param int|null $id
     * @return CategorisedFormInterface|null
     */
    public function getForm(int $id = null): ?CategorisedFormInterface;

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
     * Moves post to different thread.
     * @param int $id
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function move(int $id, ModelInterface $thread): PodiumResponse;

    /**
     * @param int $id
     * @return ArchiverInterface|null
     */
    public function getArchiver(int $id): ?ArchiverInterface;

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
     * @return LikerInterface
     */
    public function getLiker(): LikerInterface;

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
