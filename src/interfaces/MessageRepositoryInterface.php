<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface MessageRepositoryInterface extends RepositoryInterface
{
    public function getParticipant(MemberRepositoryInterface $member): MessageParticipantRepositoryInterface;

    public function isCompletelyDeleted(): bool;

    public function send(
        MemberRepositoryInterface $sender,
        MemberRepositoryInterface $receiver,
        MessageRepositoryInterface $replyTo = null,
        array $data = []
    ): bool;

    public function isProperReply(MemberRepositoryInterface $sender, MemberRepositoryInterface $receiver): bool;
}
