<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface PinnerInterface
{
    /**
     * Pins model.
     */
    public function pin($id): PodiumResponse;

    /**
     * Unpins model.
     */
    public function unpin($id): PodiumResponse;
}
