<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface MessageArchiverInterface
 * @package bizley\podium\api\interfaces
 */
interface MessageArchiverInterface extends ArchiverInterface
{
    /**
     * @param int $messageId
     * @param MembershipInterface $participant
     * @return MessageArchiverInterface|null
     */
    public static function findByMessageIdAndParticipant(
        int $messageId,
        MembershipInterface $participant
    ): ?MessageArchiverInterface;
}
