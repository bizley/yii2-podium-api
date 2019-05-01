<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface GrouperInterface
 * @package bizley\podium\api\interfaces
 */
interface GrouperInterface
{
    /**
     * Initiator of grouping.
     * @param MembershipInterface $member
     */
    public function setMember(MembershipInterface $member): void;

    /**
     * Target group.
     * @param ModelInterface $group
     */
    public function setGroup(ModelInterface $group): void;

    /**
     * Joins group.
     * @return PodiumResponse
     */
    public function join(): PodiumResponse;

    /**
     * Leaves group.
     * @return PodiumResponse
     */
    public function leave(): PodiumResponse;
}
