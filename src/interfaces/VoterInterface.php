<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface VoterInterface
{
    public function vote(
        MemberRepositoryInterface $member,
        PollRepositoryInterface $poll,
        array $answers
    ): PodiumResponse;
}
