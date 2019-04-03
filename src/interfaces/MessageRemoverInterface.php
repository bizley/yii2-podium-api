<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface MessageRemoverInterface
 * @package bizley\podium\api\interfaces
 */
interface MessageRemoverInterface extends RemoverInterface
{
    /**
     * @param int $messageId
     * @param MembershipInterface $participant
     * @return RemoverInterface|null
     */
    public static function findByMessageIdAndParticipant(int $messageId, MembershipInterface $participant): ?MessageRemoverInterface;

    /**
     * @param ModelInterface $messageHandler
     */
    public function setMessageHandler(ModelInterface $messageHandler): void;
}
