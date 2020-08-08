<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

/**
 * Interface RemoverInterface
 * @package bizley\podium\api\interfaces
 */
interface RemoverInterface
{
    /**
     * Removes model.
     * @param int $id
     * @return PodiumResponse
     */
    public function remove(int $id): PodiumResponse;
}
