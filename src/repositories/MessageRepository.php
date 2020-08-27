<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\MessageActiveRecord;
use bizley\podium\api\interfaces\MessageRepositoryInterface;
use LogicException;

final class MessageRepository implements MessageRepositoryInterface
{
    public string $activeRecordClass = MessageActiveRecord::class;

    private ?MessageActiveRecord $model = null;

    public function getActiveRecordClass(): string
    {
        return $this->activeRecordClass;
    }

    public function getModel(): MessageActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?MessageActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function getParent(): MessageRepositoryInterface
    {
        // TODO: Implement getParent() method.
    }

    public function fetchOne($messageId, $participantId): bool
    {
        // TODO: Implement fetchOne() method.
    }

    public function fetchAll($filter = null, $sort = null, $pagination = null): void
    {
        // TODO: Implement fetchAll() method.
    }

    public function getErrors(): array
    {
        // TODO: Implement getErrors() method.
    }

    public function delete(): bool
    {
        // TODO: Implement delete() method.
    }

    public function edit(array $data = []): bool
    {
        // TODO: Implement edit() method.
    }
}
