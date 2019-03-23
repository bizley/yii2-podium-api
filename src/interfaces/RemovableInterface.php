<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface RemovableInterface
 * @package bizley\podium\api\interfaces
 */
interface RemovableInterface
{
    /**
     * @param int $modelId
     * @return RemovableInterface|null
     */
    public static function findById(int $modelId): ?RemovableInterface;

    /**
     * Removes model.
     * @return PodiumResponse
     */
    public function remove(): PodiumResponse;
}
