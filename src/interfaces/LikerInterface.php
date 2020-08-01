<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface LikerInterface
 * @package bizley\podium\api\interfaces
 */
interface LikerInterface
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
     * @return PodiumResponse
     */
    public function thumbUp(): PodiumResponse;

    /**
     * Gives thumb down.
     * @return PodiumResponse
     */
    public function thumbDown(): PodiumResponse;

    /**
     * Resets thumb.
     * @return PodiumResponse
     */
    public function thumbReset(): PodiumResponse;
}
