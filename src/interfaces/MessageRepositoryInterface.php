<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface MessageRepositoryInterface
{
    public function getParent(): MessageRepositoryInterface;
    public function fetchOne($messageId, $participantId): bool;
    public function fetchAll($filter = null, $sort = null, $pagination = null): void;
    public function getErrors(): array;
    public function delete(): bool;
    public function edit(array $data = []): bool;
}
