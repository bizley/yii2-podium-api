<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface IgnorerInterface
 * @package bizley\podium\api\interfaces
 */
interface IgnorerInterface
{
    /**
     * Initiator of ignoring.
     * @param MembershipInterface $member
     */
    public function setMember(MembershipInterface $member): void;

    /**
     * Target of ignoring.
     * @param MembershipInterface $target
     */
    public function setTarget(MembershipInterface $target): void;

    /**
     * Handles ignoring process.
     * @return PodiumResponse
     */
    public function ignore(): PodiumResponse;

    /**
     * Handles unignoring process.
     * @return PodiumResponse
     */
    public function unignore(): PodiumResponse;
}
