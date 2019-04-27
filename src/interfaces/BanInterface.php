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
     * @param int $modelId
     * @return BanInterface|null
     */
    public static function findById(int $modelId): ?BanInterface;

    /**
     * @return PodiumResponse
     */
    public function ban(): PodiumResponse;

    /**
     * @return PodiumResponse
     */
    public function unban(): PodiumResponse;
}
