<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface SubscriberInterface
{
    /**
     * Subscribes the member to the thread.
     *
     * @param MembershipInterface $member
     */
    public function subscribe(MemberRepositoryInterface $member, ThreadRepositoryInterface $thread): PodiumResponse;

    /**
     * Unsubscribes the member from the thread.
     *
     * @param MembershipInterface $member
     */
    public function unsubscribe(MemberRepositoryInterface $member, ThreadRepositoryInterface $thread): PodiumResponse;
}
