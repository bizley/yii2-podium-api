<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface RankInterface
{
    /**
     * Creates rank.
     */
    public function create(array $data): PodiumResponse;

    /**
     * Updates rank.
     */
    public function edit(int $id, array $data): PodiumResponse;

    public function remove(int $id): PodiumResponse;
}
