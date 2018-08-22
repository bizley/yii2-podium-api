<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface LockableInterface
 * @package bizley\podium\api\interfaces
 */
interface LockableInterface
{
    /**
     * Locks model.
     * @return PodiumResponse
     */
    public function lock(): PodiumResponse;

    /**
     * Unlock model.
     * @return PodiumResponse
     */
    public function unlock(): PodiumResponse;
}
