<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\MessageActiveRecord;
use bizley\podium\api\interfaces\MessageRepositoryInterface;
use LogicException;

final class MessageRepository implements MessageRepositoryInterface
{
    use ActiveRecordRepositoryTrait;

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
        $message = $this->getModel()->replyTo;
        $parent = new self();
        $parent->setModel($message);

        return $parent;
    }

    public function getId(): int
    {
        return $this->getModel()->id;
    }
}
