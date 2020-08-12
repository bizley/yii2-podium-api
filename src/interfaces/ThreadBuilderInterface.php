<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface ThreadBuilderInterface
{
    public function create(array $data, MembershipInterface $author, ForumRepositoryInterface $forum): PodiumResponse;

    public function edit(int $id, array $data): PodiumResponse;
}
