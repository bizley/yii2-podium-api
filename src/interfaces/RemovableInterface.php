<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface RemovableInterface
 * @package bizley\podium\api\interfaces
 */
interface RemovableInterface
{
    /**
     * Removes model.
     * @return bool
     */
    public function remove(): bool;
}
