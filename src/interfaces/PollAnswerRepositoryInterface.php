<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface PollAnswerRepositoryInterface
{
    public function isAnswer($id): bool;

    public function getErrors(): array;

    public function create(string $answer): bool;
}
