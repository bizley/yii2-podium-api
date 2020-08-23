<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface LockerInterface
{
    /**
     * Locks the thread.
     */
    public function lock(ThreadRepositoryInterface $thread): PodiumResponse;

    /**
     * Unlock the thread.
     */
    public function unlock(ThreadRepositoryInterface $thread): PodiumResponse;
}
