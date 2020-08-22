<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface MoverInterface
{
    /**
     * Moves repository.
     */
    public function move(RepositoryInterface $repository, RepositoryInterface $parentRepository): PodiumResponse;
}
