<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface GroupInterface
{
    /**
     * Creates group.
     */
    public function create(array $data): PodiumResponse;

    /**
     * Updates group.
     */
    public function edit($id, array $data): PodiumResponse;

    public function remove($id): PodiumResponse;

    public function join($id, MemberRepositoryInterface $member): PodiumResponse;

    public function leave($id, MemberRepositoryInterface $member): PodiumResponse;
}
