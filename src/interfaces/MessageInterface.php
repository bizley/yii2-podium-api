<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;
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
     * @return PodiumResponse
     */
    public function create(array $data, MembershipInterface $sender, MembershipInterface $receiver): PodiumResponse;

    /**
     * @param RemovableInterface $messageRemover
     * @return PodiumResponse
     */
    public function remove(RemovableInterface $messageRemover): PodiumResponse;

    /**
     * @param ArchivableInterface $messageArchiver
     * @return PodiumResponse
     */
    public function archive(ArchivableInterface $messageArchiver): PodiumResponse;

    /**
     * @param ArchivableInterface $messageArchiver
     * @return PodiumResponse
     */
    public function revive(ArchivableInterface $messageArchiver): PodiumResponse;
}
