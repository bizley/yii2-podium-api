<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface ArchiverInterface
 * @package bizley\podium\api\interfaces
 */
interface ArchiverInterface
{
    /**
     * @param int $modelId
     * @return ModelInterface|null
     */
    public static function findById(int $modelId): ?ModelInterface;

    /**
     * Archives model.
     * @return PodiumResponse
     */
    public function archive(): PodiumResponse;

    /**
     * Revives model.
     * @return PodiumResponse
     */
    public function revive(): PodiumResponse;
}
