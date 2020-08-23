<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface PollRepositoryInterface extends RepositoryInterface
{
    public function create($authorId, $threadId, array $answers, array $data = []): bool;
    public function edit(array $answers = [], array $data = []): bool;
    public function isArchived(): bool;
    public function move($threadId): bool;
    public function archive(): bool;
    public function revive(): bool;
    public function getAnswerRepository(): PollAnswerRepositoryInterface;
    public function getVoteRepository(): PollVoteRepositoryInterface;
    public function hasMemberVoted($memberId): bool;
    public function isSingleChoice(): bool;
    public function vote($memberId, array $answers): bool;
}
