<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface PollBuilderInterface
{
    public function create(
        array $data,
        array $answers,
        MemberRepositoryInterface $author,
        ThreadRepositoryInterface $thread
    ): PodiumResponse;

    public function edit($id, array $data, array $answers): PodiumResponse;
}
