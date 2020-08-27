<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface MemberBuilderInterface
{
    /**
     * Registers new Podium account.
     */
    public function register($id, array $data = []): PodiumResponse;

    public function edit(MemberRepositoryInterface $member, array $data = []): PodiumResponse;

    public function activate(MemberRepositoryInterface $member): PodiumResponse;
}
