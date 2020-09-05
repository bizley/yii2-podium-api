<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use yii\data\DataFilter;
use yii\data\Pagination;
use yii\data\Sort;

interface MessageParticipantRepositoryInterface
{
    public function fetchOne(MessageRepositoryInterface $message, MemberRepositoryInterface $member): bool;

    /**
     * @param DataFilter|null            $filter
     * @param bool|array|Sort|null       $sort
     * @param bool|array|Pagination|null $pagination
     */
    public function fetchAll($filter = null, $sort = null, $pagination = null): void;

    public function getErrors(): array;

    public function delete(): bool;

    public function edit(array $data = []): bool;

    public function getParent(): MessageRepositoryInterface;

    public function isArchived(): bool;

    public function archive(): bool;

    public function revive(): bool;

    /**
     * @param int|string|array $sideId
     */
    public function copy(
        MessageRepositoryInterface $message,
        MemberRepositoryInterface $member,
        $sideId,
        array $data = []
    ): bool;
}
