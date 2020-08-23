<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface LikerInterface
{
    /**
     * Gives thumb up.
     */
    public function thumbUp(PostRepositoryInterface $post, MemberRepositoryInterface $member): PodiumResponse;

    /**
     * Gives thumb down.
     */
    public function thumbDown(PostRepositoryInterface $post, MemberRepositoryInterface $member): PodiumResponse;

    /**
     * Resets thumb.
     */
    public function thumbReset(PostRepositoryInterface $post, MemberRepositoryInterface $member): PodiumResponse;
}
