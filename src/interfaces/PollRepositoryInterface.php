<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface PollRepositoryInterface
{
    public function create(array $data, array $answers, $authorId, $threadId): bool;
    public function edit(array $data, array $answers = []): bool;
    public function isArchived(): bool;
    public function move($threadId): bool;
    public function archive(): bool;
    public function revive(): bool;
    public function getAnswerRepository(): PollAnswerRepositoryInterface;
    public function getVoteRepository(): PollVoteRepositoryInterface;
    public function hasMemberVoted($memberId): bool;
    public function isSingleChoice(): bool;
    public function vote($memberId, array $answers): bool;
    public function getId();
    public function getParent(): RepositoryInterface;
    public function fetchOne($id): bool;
    public function fetchAll($filter = null, $sort = null, $pagination = null): void;
    public function getErrors(): array;
    public function delete(): bool;
}
