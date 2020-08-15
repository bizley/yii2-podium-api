<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\PollAnswerActiveRecord;
use bizley\podium\api\interfaces\PollAnswerRepositoryInterface;
use LogicException;

final class PollAnswerRepository implements PollAnswerRepositoryInterface
{
    public string $activeRecordClass = PollAnswerActiveRecord::class;

    private ?PollAnswerActiveRecord $model = null;
    private $pollId;
    private array $errors = [];

    public function __construct($pollId)
    {
        $this->pollId = $pollId;
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

    public function isAnswer($id): bool
    {
        $modelClass = $this->activeRecordClass;
        /* @var PollAnswerActiveRecord $modelClass */
        return $modelClass::find()
            ->where(
                [
                    'id' => $id,
                    'poll_id' => $this->pollId,
                ]
            )
            ->exists();
    }

    public function create(string $answer): bool
    {
        /** @var PollAnswerActiveRecord $model */
        $model = new $this->activeRecordClass();

        $model->poll_id = $this->pollId;
        $model->answer = $answer;

        if (!$model->validate()) {
            $this->errors = $model->errors;

            return false;
        }

        return $model->save(false);
    }
}
