<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\PollVoteActiveRecord;
use bizley\podium\api\interfaces\PollVoteRepositoryInterface;
use LogicException;

final class PollVoteRepository implements PollVoteRepositoryInterface
{
    public string $activeRecordClass = PollVoteActiveRecord::class;

    private ?PollVoteActiveRecord $model = null;
    private $pollId;
    private array $errors = [];

    public function __construct($pollId)
    {
        $this->pollId = $pollId;
    }

    public function getModel(): PollVoteActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?PollVoteActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasMemberVoted($memberId): bool
    {
        /** @var PollVoteActiveRecord $modelClass */
        $modelClass = $this->activeRecordClass;
        return $modelClass::find()
            ->where(
                [
                    'member_id' => $memberId,
                    'poll_id' => $this->pollId,
                ]
            )
            ->exists();
    }

    public function register($memberId, $answerId): bool
    {
        /** @var PollVoteActiveRecord $vote */
        $vote = new $this->activeRecordClass();

        $vote->member_id = $memberId;
        $vote->answer_id = $answerId;
        $vote->poll_id = $this->pollId;

        if (!$vote->validate()) {
            $this->errors = $vote->errors;
            return false;
        }

        return $vote->save(false);
    }
}
