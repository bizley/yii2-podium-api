<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface SorterInterface
{
    /**
     * Replaces the order of the repositories.
     */
    public function replace($id, RepositoryInterface $repository): PodiumResponse;
}
