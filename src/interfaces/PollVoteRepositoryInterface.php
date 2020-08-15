<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface PollVoteRepositoryInterface
{
    public function hasMemberVoted($memberId): bool;

    public function getErrors(): array;

    public function register($memberId, $answerId): bool;
}
