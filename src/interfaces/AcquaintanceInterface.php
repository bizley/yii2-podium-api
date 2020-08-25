<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface AcquaintanceInterface
{
    /**
     * Handles befriending process.
     */
    public function befriend(MemberRepositoryInterface $member, MemberRepositoryInterface $target): PodiumResponse;

    /**
     * Handles unfriending process.
     */
    public function unfriend(MemberRepositoryInterface $member, MemberRepositoryInterface $target): PodiumResponse;

    /**
     * Handles ignoring process.
     */
    public function ignore(MemberRepositoryInterface $member, MemberRepositoryInterface $target): PodiumResponse;

    /**
     * Handles unignoring process.
     */
    public function unignore(MemberRepositoryInterface $member, MemberRepositoryInterface $target): PodiumResponse;
}
