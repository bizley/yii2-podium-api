<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;

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
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getMessages(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface;

    /**
     * Returns forum form handler.
     * @return MessageFormInterface
     */
    public function getMessageForm(): MessageFormInterface;

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
