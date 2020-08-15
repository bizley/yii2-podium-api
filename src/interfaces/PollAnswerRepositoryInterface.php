<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface PollAnswerRepositoryInterface
{
    public function isAnswer($id): bool;

    public function create(string $answer): bool;

    public function remove($id): bool;

    public function edit($id, string $answer): bool;
}
