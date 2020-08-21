<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface GrouperInterface
{
    /**
     * Joins group.
     */
    public function join($id, GroupRepositoryInterface $group): PodiumResponse;

    /**
     * Leaves group.
     */
    public function leave($id, GroupRepositoryInterface $group): PodiumResponse;
}
