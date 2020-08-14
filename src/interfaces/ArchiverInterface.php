<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface ArchiverInterface
{
    /**
     * Archives model.
     */
    public function archive($id): PodiumResponse;

    /**
     * Revives model.
     */
    public function revive($id): PodiumResponse;
}
