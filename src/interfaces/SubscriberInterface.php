<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface SubscriberInterface
{
    /**
     * Subscribes the member to the thread.
     */
    public function subscribe(ThreadRepositoryInterface $thread, MemberRepositoryInterface $member): PodiumResponse;

    /**
     * Unsubscribes the member from the thread.
     */
    public function unsubscribe(ThreadRepositoryInterface $thread, MemberRepositoryInterface $member): PodiumResponse;
}
