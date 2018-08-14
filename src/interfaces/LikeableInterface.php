<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface LikeableInterface
 * @package bizley\podium\api\interfaces
 */
interface LikeableInterface
{
    /**
     * Gives thumb up.
     * @return bool
     */
    public function thumbUp(): bool;

    /**
     * Gives thumb down.
     * @return bool
     */
    public function thumbDown(): bool;

    /**
     * Resets thumb.
     * @return bool
     */
    public function thumbReset(): bool;
}
