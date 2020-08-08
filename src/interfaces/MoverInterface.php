<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

/**
 * Interface MoverInterface
 * @package bizley\podium\api\interfaces
 */
interface MoverInterface
{
    /**
     * Moves model.
     * @param int $id
     * @param RepositoryInterface $repository
     * @return PodiumResponse
     */
    public function move(int $id, RepositoryInterface $repository): PodiumResponse;
}
