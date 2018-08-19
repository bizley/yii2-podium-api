<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface GroupingInterface
 * @package bizley\podium\api\interfaces
 */
interface GroupingInterface
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
     * @return bool
     */
    public function join(): bool;

    /**
     * Leaves group.
     * @return bool
     */
    public function leave(): bool;
}
