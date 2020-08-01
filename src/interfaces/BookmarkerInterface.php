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
     * Initiator of bookmarking.
     * @param MembershipInterface $member
     */
    public function setMember(MembershipInterface $member): void;

    /**
     * Post marker.
     * @param ModelInterface $post
     */
    public function setPost(ModelInterface $post): void;

    /**
     * Marks thread.
     * @return PodiumResponse
     */
    public function mark(): PodiumResponse;
}
