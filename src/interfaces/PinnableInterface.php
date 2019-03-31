<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface PinnableInterface
 * @package bizley\podium\api\interfaces
 */
interface PinnableInterface
{
    /**
     * @param int $modelId
     * @return PinnableInterface|null
     */
    public static function findById(int $modelId): ?PinnableInterface;

    /**
     * Pins model.
     * @return PodiumResponse
     */
    public function pin(): PodiumResponse;

    /**
     * Unpins model.
     * @return PodiumResponse
     */
    public function unpin(): PodiumResponse;
}
