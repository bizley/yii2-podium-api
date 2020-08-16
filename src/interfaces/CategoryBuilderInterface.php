<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface CategoryBuilderInterface
{
    public function create(array $data, MemberRepositoryInterface $author): PodiumResponse;

    public function edit($id, array $data): PodiumResponse;
}
