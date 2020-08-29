<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface MessageRemoverInterface
{
    public function remove(MessageRepositoryInterface $message, MemberRepositoryInterface $participant): PodiumResponse;
}
