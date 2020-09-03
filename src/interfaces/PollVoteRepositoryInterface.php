<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface PollVoteRepositoryInterface
{
    public function hasMemberVoted(MemberRepositoryInterface $member): bool;

    public function getErrors(): array;

    public function register(MemberRepositoryInterface $member, PollAnswerRepositoryInterface $answer): bool;
}
