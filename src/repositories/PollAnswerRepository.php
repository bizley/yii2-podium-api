<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\PollAnswerActiveRecord;
use bizley\podium\api\interfaces\PollAnswerRepositoryInterface;

final class PollAnswerRepository implements PollAnswerRepositoryInterface
{
    public string $activeRecordClass = PollAnswerActiveRecord::class;

    private $pollId;

    public function __construct($pollId)
    {
        $this->pollId = $pollId;
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

        return $model->save();
    }

    public function remove($id): bool
    {
        /** @var PollAnswerActiveRecord $model */
        $model = $this->activeRecordClass;
        $model::deleteAll(
            [
                'poll_id' => $this->pollId,
                'id' => $id,
            ]
        );

        return true;
    }

    public function edit($id, string $answer): bool
    {
        /** @var PollAnswerActiveRecord $modelClass */
        $modelClass = $this->activeRecordClass;
        $model = $modelClass::find()
            ->where(
                [
                    'poll_id' => $this->pollId,
                    'id' => $id,
                ]
            )
            ->one();
        if ($model === null) {
            return false;
        }

        return $model->save();
    }
}
