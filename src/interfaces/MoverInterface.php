<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface MoverInterface
{
    /**
     * Moves model.
     */
    public function move($id, RepositoryInterface $repository): PodiumResponse;
}
