<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface LockableInterface
 * @package bizley\podium\api\interfaces
 */
interface LockerInterface
{
    /**
     * @param int $modelId
     * @return LockerInterface|null
     */
    public static function findById(int $modelId): ?LockerInterface;

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
