<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\BookmarkActiveRecord;
use bizley\podium\api\interfaces\BookmarkRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
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

    public function fetchOne(MemberRepositoryInterface $member, ThreadRepositoryInterface $thread): bool
    {
        /** @var BookmarkActiveRecord $modelClass */
        $modelClass = $this->activeRecordClass;
        /** @var BookmarkActiveRecord|null $model */
        $model = $modelClass::find()
            ->where(
                [
                    'member_id' => $member->getId(),
                    'thread_id' => $thread->getId(),
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

    public function prepare(MemberRepositoryInterface $member, ThreadRepositoryInterface $thread): void
    {
        /** @var BookmarkActiveRecord $model */
        $model = new $this->activeRecordClass();

        $model->member_id = $member->getId();
        $model->thread_id = $thread->getId();

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
            return false;
        }

        return $bookmark->save(false);
    }
}
