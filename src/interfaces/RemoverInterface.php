<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface RemovableInterface
 * @package bizley\podium\api\interfaces
 */
interface RemoverInterface
{
    /**
     * @param int $modelId
     * @return RemoverInterface|null
     */
    public static function findById(int $modelId): ?RemoverInterface;

    /**
     * Removes model.
     * @return PodiumResponse
     */
    public function remove(): PodiumResponse;
}
