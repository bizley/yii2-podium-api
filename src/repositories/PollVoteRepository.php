<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\PollVoteActiveRecord;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\PollAnswerRepositoryInterface;
use bizley\podium\api\interfaces\PollRepositoryInterface;
use bizley\podium\api\interfaces\PollVoteRepositoryInterface;
use DomainException;
use LogicException;

use function is_int;

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
        $memberId = $member->getId();
        if (!is_int($memberId)) {
            throw new DomainException('Invalid member ID!');
        }
        $pollId = $this->poll->getId();
        if (!is_int($pollId)) {
            throw new DomainException('Invalid poll ID!');
        }

        /** @var PollVoteActiveRecord $modelClass */
        $modelClass = $this->activeRecordClass;

        return $modelClass::find()
            ->where(
                [
                    'member_id' => $memberId,
                    'poll_id' => $pollId,
                ]
            )
            ->exists();
    }

    public function register(MemberRepositoryInterface $member, PollAnswerRepositoryInterface $answer): bool
    {
        $memberId = $member->getId();
        if (!is_int($memberId)) {
            throw new DomainException('Invalid member ID!');
        }
        $answerId = $answer->getId();
        if (!is_int($answerId)) {
            throw new DomainException('Invalid answer ID!');
        }
        $pollId = $this->poll->getId();
        if (!is_int($pollId)) {
            throw new DomainException('Invalid poll ID!');
        }

        /** @var PollVoteActiveRecord $vote */
        $vote = new $this->activeRecordClass();

        $vote->member_id = $memberId;
        $vote->answer_id = $answerId;
        $vote->poll_id = $pollId;

        if (!$vote->validate()) {
            $this->errors = $vote->errors;

            return false;
        }

        return $vote->save(false);
    }
}
