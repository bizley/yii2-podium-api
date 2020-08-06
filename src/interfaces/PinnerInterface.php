<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface PinnerInterface
 * @package bizley\podium\api\interfaces
 */
interface PinnerInterface
{
    /**
     * Pins model.
     * @param int $id
     * @return PodiumResponse
     */
    public function pin(int $id): PodiumResponse;

    /**
     * Unpins model.
     * @param int $id
     * @return PodiumResponse
     */
    public function unpin(int $id): PodiumResponse;
}
