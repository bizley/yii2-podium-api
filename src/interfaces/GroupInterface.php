<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface GroupInterface
{
    /**
     * Creates group.
     */
    public function create(array $data = []): PodiumResponse;

    /**
     * Updates group.
     */
    public function edit(GroupRepositoryInterface $group, array $data = []): PodiumResponse;

    public function remove(GroupRepositoryInterface $group): PodiumResponse;

    public function join(GroupRepositoryInterface $group, MemberRepositoryInterface $member): PodiumResponse;

    public function leave(GroupRepositoryInterface $group, MemberRepositoryInterface $member): PodiumResponse;
}
