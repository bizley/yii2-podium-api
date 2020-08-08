<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

/**
 * Interface RegistererInterface
 * @package bizley\podium\api\interfaces
 */
interface RegistererInterface
{
    /**
     * Loads registration data.
     * @param array $data account data
     * @return bool
     */
    public function loadData(array $data = []): bool;

    /**
     * Registers new Podium account.
     * @return PodiumResponse
     */
    public function register(): PodiumResponse;
}
