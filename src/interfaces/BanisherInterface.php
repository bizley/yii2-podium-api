<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface BanisherInterface
{
    public function ban(MemberRepositoryInterface $member): PodiumResponse;

    public function unban(MemberRepositoryInterface $member): PodiumResponse;
}
