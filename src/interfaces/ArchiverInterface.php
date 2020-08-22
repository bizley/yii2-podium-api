<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface ArchiverInterface
{
    /**
     * Archives the repository.
     */
    public function archive(RepositoryInterface $repository): PodiumResponse;

    /**
     * Revives the repository.
     */
    public function revive(RepositoryInterface $repository): PodiumResponse;
}
