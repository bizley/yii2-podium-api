<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

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
     * @return PodiumResponse
     */
    public function befriend(): PodiumResponse;

    /**
     * Handles unfriending process.
     * @return PodiumResponse
     */
    public function unfriend(): PodiumResponse;
}
