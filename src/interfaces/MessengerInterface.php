<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface MessengerInterface
 * @package bizley\podium\api\interfaces
 */
interface MessengerInterface extends ModelFormInterface
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

    /**
     * @param MessageParticipantModelInterface|null $replyTo
     */
    public function setReplyTo(?MessageParticipantModelInterface $replyTo): void;
}
