<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\BookmarkActiveRecord;
use bizley\podium\api\interfaces\BookmarkRepositoryInterface;
use LogicException;

final class BookmarkRepository implements BookmarkRepositoryInterface
{
    public string $activeRecordClass = BookmarkActiveRecord::class;

    private array $errors = [];
    private ?BookmarkActiveRecord $model = null;

    public function getModel(): BookmarkActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?BookmarkActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function fetchOne(int $memberId, int $threadId): bool
    {
        /** @var BookmarkActiveRecord $modelClass */
        $modelClass = $this->activeRecordClass;
        /** @var BookmarkActiveRecord|null $model */
        $model = $modelClass::find()
            ->where(
                [
                    'member_id' => $memberId,
                    'thread_id' => $threadId,
                ]
            )
            ->one();
        if (null === $model) {
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
        $model = new $this->activeRecordClass();
        $model->member_id = $memberId;
        $model->thread_id = $threadId;
        $this->model = $model;
    }

    public function getLastSeen(): ?int
    {
        return $this->getModel()->last_seen;
    }

    public function mark(int $timeMark): bool
    {
        $bookmark = $this->getModel();
        $bookmark->last_seen = $timeMark;

        if (!$bookmark->validate()) {
            $this->errors = $bookmark->errors;
        }

        return $bookmark->save(false);
    }
}
