<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface LikeableInterface
 * @package bizley\podium\api\interfaces
 */
interface LikeableInterface
{
    /**
     * Gives thumb up.
     * @return PodiumResponse
     */
    public function thumbUp(): PodiumResponse;

    /**
     * Gives thumb down.
     * @return PodiumResponse
     */
    public function thumbDown(): PodiumResponse;

    /**
     * Resets thumb.
     * @return PodiumResponse
     */
    public function thumbReset(): PodiumResponse;
}
