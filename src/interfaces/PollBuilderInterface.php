<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface PollBuilderInterface
{
    public function create(
        MemberRepositoryInterface $author,
        ThreadRepositoryInterface $thread,
        array $answers,
        array $data = []
    ): PodiumResponse;

    public function edit(PollRepositoryInterface $poll, array $answers = [], array $data = []): PodiumResponse;
}
