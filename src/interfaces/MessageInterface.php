<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface MessageInterface
 * @package bizley\podium\api\interfaces
 */
interface MessageInterface
{
    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getMessageById(int $id): ?ModelInterface;

    /**
     * Returns forum form handler.
     * @return ModelFormInterface
     */
    public function getMessageForm(): ModelFormInterface;

    /**
     * Creates message.
     * @param array $data
     * @param MembershipInterface $sender
     * @param MembershipInterface $receiver
     * @return bool
     */
    public function create(array $data, MembershipInterface $sender, MembershipInterface $receiver): bool;

    /**
     * @param RemovableInterface $messageRemover
     * @return bool
     */
    public function remove(RemovableInterface $messageRemover): bool;

    /**
     * @param ArchivableInterface $messageArchiver
     * @return bool
     */
    public function archive(ArchivableInterface $messageArchiver): bool;

    /**
     * @param ArchivableInterface $messageArchiver
     * @return bool
     */
    public function revive(ArchivableInterface $messageArchiver): bool;
}
