<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface CategoryBuilderInterface
{
    public function create(MemberRepositoryInterface $author, array $data = []): PodiumResponse;

    public function edit(CategoryRepositoryInterface $category, array $data = []): PodiumResponse;
}
