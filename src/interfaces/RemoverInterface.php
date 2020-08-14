<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface RemoverInterface
{
    /**
     * Removes model.
     */
    public function remove($id): PodiumResponse;
}
