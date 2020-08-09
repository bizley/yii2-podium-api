<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface BookmarkerInterface
{
    /**
     * Marks thread.
     */
    public function mark(MemberRepositoryInterface $member, PostRepositoryInterface $post): PodiumResponse;
}
