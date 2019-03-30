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
    public function getById(int $id): ?ModelInterface;

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getAll(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface;

    /**
     * Returns forum form handler.
     * @return SendingInterface
     */
    public function getMailer(): SendingInterface;

    /**
     * Sends message.
     * @param array $data
     * @param MembershipInterface $sender
     * @param MembershipInterface $receiver
     * @param MessageParticipantModelInterface $replyTo
     * @return PodiumResponse
     */
    public function send(array $data, MembershipInterface $sender, MembershipInterface $receiver, ?MessageParticipantModelInterface $replyTo = null): PodiumResponse;

    /**
     * @param int $id
     * @param string $side
     * @return MessageRemoverInterface|null
     */
    public function getRemover(int $id, string $side): ?MessageRemoverInterface;

    /**
     * @param int $id
     * @param string $side
     * @return PodiumResponse
     */
    public function remove(int $id, string $side): PodiumResponse;

    /**
     * @param ArchivableInterface $messageParticipantArchiver
     * @return PodiumResponse
     */
    public function archive(ArchivableInterface $messageParticipantArchiver): PodiumResponse;

    /**
     * @param ArchivableInterface $messageParticipantArchiver
     * @return PodiumResponse
     */
    public function revive(ArchivableInterface $messageParticipantArchiver): PodiumResponse;
}
