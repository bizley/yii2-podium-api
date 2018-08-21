<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface ModelFormInterface
 * @package bizley\podium\api\interfaces
 */
interface MessageFormInterface extends ModelFormInterface
{
    /**
     * @param MembershipInterface $sender
     */
    public function setSender(MembershipInterface $sender): void;

    /**
     * @param MembershipInterface $receiver
     */
    public function setReceiver(MembershipInterface $receiver): void;
}
