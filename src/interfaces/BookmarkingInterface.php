<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface BookmarkingInterface
 * @package bizley\podium\api\interfaces
 */
interface BookmarkingInterface
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
     * @return bool
     */
    public function mark(): bool;
}
