<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface BanInterface
 * @package bizley\podium\api\interfaces
 */
interface BanInterface
{
    /**
     * @return bool
     */
    public function ban(): bool;

    /**
     * @return bool
     */
    public function unban(): bool;
}
