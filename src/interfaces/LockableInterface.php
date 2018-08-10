<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface LockableInterface
 * @package bizley\podium\api\interfaces
 */
interface LockableInterface
{
    /**
     * Locks model.
     * @return bool
     */
    public function lock(): bool;

    /**
     * Unlock model.
     * @return bool
     */
    public function unlock(): bool;
}
