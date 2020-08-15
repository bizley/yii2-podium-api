<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface PollAnswerRepositoryInterface
{
    public function isAnswer($id): bool;
}
