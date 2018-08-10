<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface PinnableInterface
 * @package bizley\podium\api\interfaces
 */
interface PinnableInterface
{
    /**
     * Pins model.
     * @return bool
     */
    public function pin(): bool;

    /**
     * Unpins model.
     * @return bool
     */
    public function unpin(): bool;
}
