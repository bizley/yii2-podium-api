<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface ModelFormInterface
 * @package bizley\podium\api\interfaces
 */
interface ModelFormInterface
{
    /**
     * @param int $modelFormId
     * @return ModelFormInterface|null
     */
    public static function findById(int $modelFormId): ?ModelFormInterface;

    /**
     * Loads form data.
     * @param array|null $data form data
     * @return bool
     */
    public function loadData(array $data = []): bool;

    /**
     * Creates new model.
     * @return PodiumResponse
     */
    public function create(): PodiumResponse;

    /**
     * Updates model.
     * @return PodiumResponse
     */
    public function edit(): PodiumResponse;
}
