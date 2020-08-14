<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\PollActiveRecord;
use bizley\podium\api\interfaces\PollRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use LogicException;

final class PollRepository implements PollRepositoryInterface
{
    use ActiveRecordRepositoryTrait;

    public string $activeRecordClass = PollActiveRecord::class;

    private ?PollActiveRecord $model = null;

    public function getActiveRecordClass(): string
    {
        return $this->activeRecordClass;
    }

    public function getModel(): PollActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?PollActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function getId(): int
    {
        return $this->getModel()->id;
    }

    public function getParent(): RepositoryInterface
    {
        $threadRepository = $this->getModel()->thread;
        $parent = new ThreadRepository();
        $parent->setModel($threadRepository);

        return $parent;
    }

    public function isArchived(): bool
    {
        return $this->getModel()->archived;
    }

    public function create(array $data, $authorId, $threadId): bool
    {
        /** @var PollActiveRecord $poll */
        $poll = new $this->activeRecordClass();
        if (!$poll->load($data, '')) {
            return false;
        }

        $poll->author_id = $authorId;
        $poll->thread_id = $threadId;

        if (!$poll->validate()) {
            $this->errors = $poll->errors;
        }

        return $poll->save(false);
    }

    public function move($threadId): bool
    {
        $poll = $this->getModel();
        $poll->thread_id = $threadId;
        if (!$poll->validate()) {
            $this->errors = $poll->errors;
        }

        return $poll->save(false);
    }

    public function archive(): bool
    {
        $poll = $this->getModel();
        $poll->archived = true;
        if (!$poll->validate()) {
            $this->errors = $poll->errors;
        }

        return $poll->save(false);
    }

    public function revive(): bool
    {
        $poll = $this->getModel();
        $poll->archived = false;
        if (!$poll->validate()) {
            $this->errors = $poll->errors;
        }

        return $poll->save(false);
    }
}
