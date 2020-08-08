<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

/**
 * Interface SubscriberInterface
 * @package bizley\podium\api\interfaces
 */
interface SubscriberInterface
{
    /**
     * Subscribes the member to the thread.
     * @param MembershipInterface $member
     * @param ThreadRepositoryInterface $thread
     * @return PodiumResponse
     */
    public function subscribe(MembershipInterface $member, ThreadRepositoryInterface $thread): PodiumResponse;

    /**
     * Unsubscribes the member from the thread.
     * @param MembershipInterface $member
     * @param ThreadRepositoryInterface $thread
     * @return PodiumResponse
     */
    public function unsubscribe(MembershipInterface $member, ThreadRepositoryInterface $thread): PodiumResponse;
}
