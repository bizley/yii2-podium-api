<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface RemoverInterface
 * @package bizley\podium\api\interfaces
 */
interface RemoverInterface
{
    /**
     * @param int $modelId
     * @return ModelInterface|null
     */
    public static function findById(int $modelId): ?ModelInterface;

    /**
     * Removes model.
     * @return PodiumResponse
     */
    public function remove(): PodiumResponse;
}
