<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface MessageInterface
{
    public function send(
        array $data,
        MemberRepositoryInterface $sender,
        MemberRepositoryInterface $receiver,
        MessageParticipantModelInterface $replyTo = null
    ): PodiumResponse;

    public function remove(int $id, MembershipInterface $participant): PodiumResponse;

    public function archive(int $id, MembershipInterface $participant): PodiumResponse;

    public function revive(int $id, MembershipInterface $participant): PodiumResponse;
}
