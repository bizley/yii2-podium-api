<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface AcquaintanceInterface
{
    /**
     * Handles befriending process.
     */
    public function befriend($id, MemberRepositoryInterface $member): PodiumResponse;

    /**
     * Handles unfriending process.
     */
    public function unfriend($id, MemberRepositoryInterface $member): PodiumResponse;

    /**
     * Handles ignoring process.
     */
    public function ignore($id, MemberRepositoryInterface $member): PodiumResponse;

    /**
     * Handles unignoring process.
     */
    public function unignore($id, MemberRepositoryInterface $member): PodiumResponse;
}
