<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface MessageRepositoryInterface extends RepositoryInterface
{
    public function getParticipant(MemberRepositoryInterface $member): MessageParticipantRepositoryInterface;

    public function isCompletelyDeleted(): bool;
}
