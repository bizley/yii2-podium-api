<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface PinnerInterface
{
    /**
     * Pins model.
     */
    public function pin(RepositoryInterface $thread): PodiumResponse;

    /**
     * Unpins model.
     */
    public function unpin(RepositoryInterface $thread): PodiumResponse;
}
