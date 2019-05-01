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
     * @param int $modelId
     * @return ModelInterface|null
     */
    public static function findById(int $modelId): ?ModelInterface;

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
