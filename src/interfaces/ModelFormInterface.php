<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface ModelFormInterface
 * @package bizley\podium\api\interfaces
 */
interface ModelFormInterface
{
    /**
     * Loads form data.
     * @param array|null $data form data
     * @return bool
     */
    public function loadData(?array $data = null): bool;

    /**
     * Creates new model.
     * @return bool
     */
    public function create(): bool;

    /**
     * Updates model.
     * @return bool
     */
    public function edit(): bool;
}
