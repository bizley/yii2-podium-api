<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface MessageInterface
{
    public function send(
        MemberRepositoryInterface $sender,
        MemberRepositoryInterface $receiver,
        MessageRepositoryInterface $replyTo = null,
        array $data = []
    ): PodiumResponse;

    public function remove(MessageRepositoryInterface $message, MemberRepositoryInterface $participant): PodiumResponse;

    public function archive(
        MessageRepositoryInterface $message,
        MemberRepositoryInterface $participant
    ): PodiumResponse;

    public function revive(MessageRepositoryInterface $message, MemberRepositoryInterface $participant): PodiumResponse;
}
