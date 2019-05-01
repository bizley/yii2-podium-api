<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface BanisherInterface
 * @package bizley\podium\api\interfaces
 */
interface BanisherInterface
{
    /**
     * @param int $modelId
     * @return ModelInterface|null
     */
    public static function findById(int $modelId): ?ModelInterface;

    /**
     * @return PodiumResponse
     */
    public function ban(): PodiumResponse;

    /**
     * @return PodiumResponse
     */
    public function unban(): PodiumResponse;
}
