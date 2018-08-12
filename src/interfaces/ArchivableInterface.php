<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface RemovableInterface
 * @package bizley\podium\api\interfaces
 */
interface ArchivableInterface
{
    /**
     * Archives model.
     * @return bool
     */
    public function archive(): bool;

    /**
     * Revives model.
     * @return bool
     */
    public function revive(): bool;
}
