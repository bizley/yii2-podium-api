<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

/**
 * Interface ArchiverInterface
 * @package bizley\podium\api\interfaces
 */
interface ArchiverInterface
{
    /**
     * Archives model.
     * @param int $id
     * @return PodiumResponse
     */
    public function archive(int $id): PodiumResponse;

    /**
     * Revives model.
     * @param int $id
     * @return PodiumResponse
     */
    public function revive(int $id): PodiumResponse;
}
