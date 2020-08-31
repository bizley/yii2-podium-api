<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface MessageParticipantRepositoryInterface
{
    public function fetchOne($messageId, $memberId): bool;

    public function fetchAll($filter = null, $sort = null, $pagination = null): void;

    public function getErrors(): array;

    public function delete(): bool;

    public function edit(array $data = []): bool;

    public function getParent(): MessageRepositoryInterface;

    public function isArchived(): bool;

    public function archive(): bool;

    public function revive(): bool;
}
