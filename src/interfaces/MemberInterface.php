<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;

/**
 * Interface MemberComponentInterface
 * @package bizley\podium\api\interfaces
 */
interface MemberInterface
{
    /**
     * @param int $id
     * @return MembershipInterface|null
     */
    public function getMemberById(int $id): ?MembershipInterface;

    /**
     * @param int|string $id
     * @return MembershipInterface|null
     */
    public function getMemberByUserId($id): ?MembershipInterface;

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getMembers(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface;

    /**
     * Returns registration handler.
     * @return RegistrationInterface
     */
    public function getRegistration(): RegistrationInterface;

    /**
     * Registers account.
     * @param array $data
     * @return PodiumResponse
     */
    public function register(array $data): PodiumResponse;

    /**
     * @param RemovableInterface $memberRemover
     * @return PodiumResponse
     */
    public function remove(RemovableInterface $memberRemover): PodiumResponse;

    /**
     * @param ModelFormInterface $member
     * @param array $data
     * @return PodiumResponse
     */
    public function edit(ModelFormInterface $member, array $data): PodiumResponse;

    /**
     * Returns friendship handler.
     * @return FriendshipInterface
     */
    public function getFriendship(): FriendshipInterface;

    /**
     * Makes target friend of member.
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function befriend(MembershipInterface $member, MembershipInterface $target): PodiumResponse;

    /**
     * Makes target member friend no more.
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function unfriend(MembershipInterface $member, MembershipInterface $target): PodiumResponse;

    /**
     * Returns ignoring handler.
     * @return IgnoringInterface
     */
    public function getIgnoring(): IgnoringInterface;

    /**
     * Sets target as ignored by member.
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function ignore(MembershipInterface $member, MembershipInterface $target): PodiumResponse;

    /**
     * Sets target as unignored by member.
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function unignore(MembershipInterface $member, MembershipInterface $target): PodiumResponse;

    /**
     * @param BanInterface $member
     * @return PodiumResponse
     */
    public function ban(BanInterface $member): PodiumResponse;

    /**
     * @param BanInterface $member
     * @return PodiumResponse
     */
    public function unban(BanInterface $member): PodiumResponse;
}
