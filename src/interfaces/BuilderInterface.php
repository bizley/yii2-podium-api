<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface BuilderInterface
{
    public function create(array $data, MemberRepositoryInterface $author, RepositoryInterface $parent): PodiumResponse;

    public function edit(int $id, array $data): PodiumResponse;
}
