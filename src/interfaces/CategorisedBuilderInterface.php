<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface CategorisedBuilderInterface
{
    public function create(array $data, MemberRepositoryInterface $author, RepositoryInterface $parent): PodiumResponse;

    public function edit($id, array $data): PodiumResponse;
}
