<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface BookmarkerInterface
 * @package bizley\podium\api\interfaces
 */
interface BookmarkerInterface
{
    /**
     * Marks thread.
     * @param MembershipInterface $member
     * @param PostRepositoryInterface $post
     * @return PodiumResponse
     */
    public function mark(MembershipInterface $member, PostRepositoryInterface $post): PodiumResponse;
}
