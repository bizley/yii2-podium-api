<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface FriendshipInterface
 * @package bizley\podium\api\interfaces
 */
interface FriendshipInterface
{
    /**
     * Initiator of friendship.
     * @param MembershipInterface $member
     */
    public function setMember(MembershipInterface $member): void;

    /**
     * Target of friendship.
     * @param MembershipInterface $target
     */
    public function setTarget(MembershipInterface $target): void;

    /**
     * Handles befriending process.
     * @return bool whether befriending was successful
     */
    public function befriend(): bool;

    /**
     * Handles unfriending process.
     * @return bool whether unfriending was successful
     */
    public function unfriend(): bool;

    /**
     * @return bool whether target is a friend of member
     */
    public function isFriend(): bool;
}
