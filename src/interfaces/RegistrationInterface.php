<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface AccountInterface
 * @package bizley\podium\api\interfaces
 */
interface RegistrationInterface
{
    /**
     * Loads registration data.
     * @param array|null $data account data
     * @return bool
     */
    public function loadData(?array $data = null): bool;

    /**
     * Registers new Podium account.
     * @return bool
     */
    public function register(): bool;
}
