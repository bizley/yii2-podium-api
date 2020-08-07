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
     * @param PostRepositoryInterface $post
     * @param MembershipInterface $member
     * @return PodiumResponse
     */
    public function mark(PostRepositoryInterface $post, MembershipInterface $member): PodiumResponse;
}
