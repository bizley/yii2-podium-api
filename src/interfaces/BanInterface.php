<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface BanInterface
 * @package bizley\podium\api\interfaces
 */
interface BanInterface
{
    /**
     * @return PodiumResponse
     */
    public function ban(): PodiumResponse;

    /**
     * @return PodiumResponse
     */
    public function unban(): PodiumResponse;
}
