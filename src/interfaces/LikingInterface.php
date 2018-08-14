<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface LikingInterface
 * @package bizley\podium\api\interfaces
 */
interface LikingInterface
{
    /**
     * Initiator of liking.
     * @param MembershipInterface $member
     */
    public function setMember(MembershipInterface $member): void;

    /**
     * Target of liking.
     * @param ModelInterface $post
     */
    public function setPost(ModelInterface $post): void;

    /**
     * Gives thumb up.
     * @return bool
     */
    public function thumbUp(): bool;

    /**
     * Gives thumb down.
     * @return bool
     */
    public function thumbDown(): bool;

    /**
     * Resets thumb.
     * @return bool
     */
    public function thumbReset(): bool;
}
