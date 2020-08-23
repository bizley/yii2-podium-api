<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface PinnerInterface
{
    /**
     * Pins model.
     */
    public function pin(ThreadRepositoryInterface $thread): PodiumResponse;

    /**
     * Unpins model.
     */
    public function unpin(ThreadRepositoryInterface $thread): PodiumResponse;
}
