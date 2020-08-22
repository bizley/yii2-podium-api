<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface KeeperInterface
{
    /**
     * Joins group.
     */
    public function join(GroupRepositoryInterface $group, MemberRepositoryInterface $member): PodiumResponse;

    /**
     * Leaves group.
     */
    public function leave(GroupRepositoryInterface $group, MemberRepositoryInterface $member): PodiumResponse;
}
