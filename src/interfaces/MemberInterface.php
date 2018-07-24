<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use yii\rbac\Permission;
use yii\rbac\Role;

/**
 * Interface MemberComponentInterface
 * @package bizley\podium\api\interfaces
 */
interface MemberInterface
{
    /**
     * Returns member handler.
     * @return MembershipInterface
     */
    public function getMembership(): MembershipInterface;

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
     * Returns registration handler.
     * @return RegistrationInterface
     */
    public function getRegistration(): RegistrationInterface;

    /**
     * Registers account.
     * @param array $data
     * @return bool
     */
    public function register(array $data): bool;

    /**
     * Returns friendship handler.
     * @return FriendshipInterface
     */
    public function getFriendship(): FriendshipInterface;

    /**
     * Makes target friend of member.
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return bool
     */
    public function befriend(MembershipInterface $member, MembershipInterface $target): bool;

    /**
     * Makes target member friend no more.
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return bool
     */
    public function unfriend(MembershipInterface $member, MembershipInterface $target): bool;

    /**
     * Returns ignoring handler.
     * @return IgnoringInterface
     */
    public function getIgnoring(): IgnoringInterface;

    /**
     * Sets target as ignored by member.
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return bool
     */
    public function ignore(MembershipInterface $member, MembershipInterface $target): bool;

    /**
     * Sets target as unignored by member.
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return bool
     */
    public function unignore(MembershipInterface $member, MembershipInterface $target): bool;

    /**
     * Returns assigning handler.
     * @return AssigningInterface
     */
    public function getAssigning(): AssigningInterface;

    /**
     * @param MembershipInterface $member
     * @param Role|Permission $role
     * @return bool
     */
    public function assign(MembershipInterface $member, $role): bool;
}
