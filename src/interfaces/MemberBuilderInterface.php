<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface MemberBuilderInterface
{
    /**
     * Registers new Podium account.
     */
    public function register(array $data): PodiumResponse;
}
