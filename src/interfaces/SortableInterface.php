<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface SortableInterface
 * @package bizley\podium\api\interfaces
 */
interface SortableInterface
{
    /**
     * Loads sorter data.
     * @param array $data
     * @return bool
     */
    public function loadData(array $data = []): bool;

    /**
     * Sorts models.
     * @return bool
     */
    public function sort(): bool;
}
