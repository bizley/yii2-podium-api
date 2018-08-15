<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface SubscribingInterface
 * @package bizley\podium\api\interfaces
 */
interface SubscribingInterface
{
    /**
     * Initiator of subscribing.
     * @param MembershipInterface $member
     */
    public function setMember(MembershipInterface $member): void;

    /**
     * Target of subscribing.
     * @param ModelInterface $thread
     */
    public function setThread(ModelInterface $thread): void;

    /**
     * Subscribes.
     * @return bool
     */
    public function subscribe(): bool;

    /**
     * Unsubscribes.
     * @return bool
     */
    public function unsubscribe(): bool;
}
