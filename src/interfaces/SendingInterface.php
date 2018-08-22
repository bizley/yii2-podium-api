<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface ModelFormInterface
 * @package bizley\podium\api\interfaces
 */
interface SendingInterface extends ModelFormInterface
{
    /**
     * @param MembershipInterface $sender
     */
    public function setSender(MembershipInterface $sender): void;

    /**
     * @param MembershipInterface $receiver
     */
    public function setReceiver(MembershipInterface $receiver): void;

    /**
     * @return PodiumResponse
     */
    public function send(): PodiumResponse;
}
