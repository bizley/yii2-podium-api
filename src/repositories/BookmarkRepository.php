<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\BookmarkActiveRecord;
use bizley\podium\api\interfaces\BookmarkRepositoryInterface;
use LogicException;

final class BookmarkRepository implements BookmarkRepositoryInterface
{
    public string $bookmarkActiveRecord = BookmarkActiveRecord::class;

    private array $errors = [];
    private ?BookmarkActiveRecord $model = null;

    public function find(int $memberId, int $threadId): bool
    {
        /** @var BookmarkActiveRecord $modelClass */
        $modelClass = $this->bookmarkActiveRecord;
        /** @var BookmarkActiveRecord|null $model */
        $model = $modelClass::find()
            ->where(
                [
                    'member_id' => $memberId,
                    'thread_id' => $threadId,
                ]
            )
            ->one();
        if ($model === null) {
            return false;
        }
        $this->model = $model;
        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function create(int $memberId, int $threadId): void
    {
        /** @var BookmarkActiveRecord $model */
        $model = new $this->bookmarkActiveRecord();
        $model->member_id = $memberId;
        $model->thread_id = $threadId;
        $this->model = $model;
    }

    public function getLastSeen(): ?int
    {
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }
        return $this->model->last_seen;
    }

    public function mark(int $timeMark): bool
    {
        if ($this->model === null) {
            throw new LogicException('You need to call find() first!');
        }

        $this->model->last_seen = $timeMark;

        if (!$this->model->validate()) {
            $this->errors = $this->model->errors;
        }

        return $this->model->save(false);
    }
}
