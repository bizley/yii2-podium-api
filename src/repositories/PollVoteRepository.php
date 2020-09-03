<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\PollVoteActiveRecord;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\PollAnswerRepositoryInterface;
use bizley\podium\api\interfaces\PollRepositoryInterface;
use bizley\podium\api\interfaces\PollVoteRepositoryInterface;
use LogicException;

final class PollVoteRepository implements PollVoteRepositoryInterface
{
    public string $activeRecordClass = PollVoteActiveRecord::class;

    private ?PollVoteActiveRecord $model = null;
    private PollRepositoryInterface $poll;
    private array $errors = [];

    public function __construct(PollRepositoryInterface $poll)
    {
        $this->poll = $poll;
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

    public function hasMemberVoted(MemberRepositoryInterface $member): bool
    {
        /** @var PollVoteActiveRecord $modelClass */
        $modelClass = $this->activeRecordClass;

        return $modelClass::find()
            ->where(
                [
                    'member_id' => $member->getId(),
                    'poll_id' => $this->poll->getId(),
                ]
            )
            ->exists();
    }

    public function register(MemberRepositoryInterface $member, PollAnswerRepositoryInterface $answer): bool
    {
        /** @var PollVoteActiveRecord $vote */
        $vote = new $this->activeRecordClass();

        $vote->member_id = $member->getId();
        $vote->answer_id = $answer->getId();
        $vote->poll_id = $this->poll->getId();

        if (!$vote->validate()) {
            $this->errors = $vote->errors;

            return false;
        }

        return $vote->save(false);
    }
}
