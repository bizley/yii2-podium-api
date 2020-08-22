<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface MemberRepositoryInterface extends RepositoryInterface
{
    public function ban(): bool;

    public function unban(): bool;
}
