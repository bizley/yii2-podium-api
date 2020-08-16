<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface SorterInterface
{
    /**
     * Sorts models.
     */
    public function replace($id, CategoryRepositoryInterface $category): PodiumResponse;
}
