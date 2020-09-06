<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\PollAnswerActiveRecord;
use bizley\podium\api\interfaces\PollAnswerRepositoryInterface;
use bizley\podium\api\interfaces\PollRepositoryInterface;
use DomainException;
use LogicException;

use function is_int;

final class PollAnswerRepository implements PollAnswerRepositoryInterface
{
    public string $activeRecordClass = PollAnswerActiveRecord::class;

    private ?PollAnswerActiveRecord $model = null;

    private PollRepositoryInterface $poll;

    private array $errors = [];

    public function __construct(PollRepositoryInterface $poll)
    {
        $this->poll = $poll;
    }

    public function getModel(): PollAnswerActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?PollAnswerActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getId(): int
    {
        return $this->getModel()->id;
    }

    /**
     * @param int $id
     */
    public function isAnswer($id): bool
    {
        if (!is_int($id)) {
            throw new DomainException('Invalid answer ID!');
        }
        $pollId = $this->poll->getId();
        if (!is_int($pollId)) {
            throw new DomainException('Invalid poll ID!');
        }

        $modelClass = $this->activeRecordClass;
        /* @var PollAnswerActiveRecord $modelClass */
        return $modelClass::find()
            ->where(
                [
                    'id' => $id,
                    'poll_id' => $pollId,
                ]
            )
            ->exists();
    }

    public function create(string $answer): bool
    {
        $pollId = $this->poll->getId();
        if (!is_int($pollId)) {
            throw new DomainException('Invalid poll ID!');
        }

        /** @var PollAnswerActiveRecord $model */
        $model = new $this->activeRecordClass();

        $model->poll_id = $pollId;
        $model->answer = $answer;

        return $model->save();
    }

    /**
     * @param int $id
     */
    public function remove($id): bool
    {
        if (!is_int($id)) {
            throw new DomainException('Invalid answer ID!');
        }
        $pollId = $this->poll->getId();
        if (!is_int($pollId)) {
            throw new DomainException('Invalid poll ID!');
        }

        /** @var PollAnswerActiveRecord $model */
        $model = $this->activeRecordClass;
        $model::deleteAll(
            [
                'poll_id' => $pollId,
                'id' => $id,
            ]
        );

        return true;
    }

    /**
     * @param int $id
     */
    public function edit($id, string $answer): bool
    {
        if (!is_int($id)) {
            throw new DomainException('Invalid answer ID!');
        }
        $pollId = $this->poll->getId();
        if (!is_int($pollId)) {
            throw new DomainException('Invalid poll ID!');
        }

        /** @var PollAnswerActiveRecord $modelClass */
        $modelClass = $this->activeRecordClass;
        /** @var PollAnswerActiveRecord|null $model */
        $model = $modelClass::find()
            ->where(
                [
                    'poll_id' => $pollId,
                    'id' => $id,
                ]
            )
            ->one();
        if (null === $model) {
            return false;
        }

        return $model->save();
    }
}
