<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface LikerInterface
{
    /**
     * Gives thumb up.
     */
    public function thumbUp(MemberRepositoryInterface $member, PostRepositoryInterface $post): PodiumResponse;

    /**
     * Gives thumb down.
     */
    public function thumbDown(MemberRepositoryInterface $member, PostRepositoryInterface $post): PodiumResponse;

    /**
     * Resets thumb.
     */
    public function thumbReset(MemberRepositoryInterface $member, PostRepositoryInterface $post): PodiumResponse;
}
