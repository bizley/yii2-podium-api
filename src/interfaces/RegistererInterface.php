<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface RegistrationInterface
 * @package bizley\podium\api\interfaces
 */
interface RegistererInterface
{
    /**
     * Loads registration data.
     * @param array|null $data account data
     * @return bool
     */
    public function loadData(?array $data = null): bool;

    /**
     * Registers new Podium account.
     * @return PodiumResponse
     */
    public function register(): PodiumResponse;
}
