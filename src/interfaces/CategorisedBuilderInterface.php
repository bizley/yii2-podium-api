<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface CategorisedBuilderInterface
{
    public function create(
        MemberRepositoryInterface $author,
        RepositoryInterface $parent,
        array $data = []
    ): PodiumResponse;

    public function edit(RepositoryInterface $repository, array $data = []): PodiumResponse;
}
