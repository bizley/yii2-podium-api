<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface MessageFormInterface
 * @package bizley\podium\api\interfaces
 */
interface MessageFormInterface
{
    /**
     * Creates new model.
     * @return PodiumResponse
     */
    public function create(): PodiumResponse;

    /**
     * @return PodiumResponse
     */
    public function markRead(): PodiumResponse;

    /**
     * @return PodiumResponse
     */
    public function markReplied(): PodiumResponse;

    /**
     * @param int $senderId
     * @param int $replyId
     * @return MessageFormInterface|null
     */
    public function findMessageToReply(int $senderId, int $replyId): ?MessageFormInterface;

    /**
     * @param int $senderId
     */
    public function setSenderId(int $senderId): void;

    /**
     * @param int $messageId
     */
    public function setMessageId(int $messageId): void;

    /**
     * @param string $statusId
     */
    public function setStatusId(string $statusId): void;

    /**
     * @param string $sideId
     */
    public function setSideId(string $sideId): void;
}
