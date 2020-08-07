<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface LockerInterface
 * @package bizley\podium\api\interfaces
 */
interface LockerInterface
{
    /**
     * Locks model.
     * @param int $id
     * @return PodiumResponse
     */
    public function lock(int $id): PodiumResponse;

    /**
     * Unlock model.
     * @param int $id
     * @return PodiumResponse
     */
    public function unlock(int $id): PodiumResponse;
}
