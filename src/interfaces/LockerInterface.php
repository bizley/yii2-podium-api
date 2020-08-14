<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface LockerInterface
{
    /**
     * Locks model.
     */
    public function lock($id): PodiumResponse;

    /**
     * Unlock model.
     */
    public function unlock($id): PodiumResponse;
}
