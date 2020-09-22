<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface PollRepositoryInterface extends RepositoryInterface
{
    public function create(
        MemberRepositoryInterface $author,
        ThreadRepositoryInterface $thread,
        array $data,
        array $answers = []
    ): bool;

    public function edit(array $answers = [], array $data = []): bool;

    public function isArchived(): bool;

    public function move(ThreadRepositoryInterface $thread): bool;

    public function archive(): bool;

    public function revive(): bool;

    public function getAnswerRepository(): PollAnswerRepositoryInterface;

    public function getVoteRepository(): PollVoteRepositoryInterface;

    public function hasMemberVoted(MemberRepositoryInterface $member): bool;

    public function isSingleChoice(): bool;

    public function vote(MemberRepositoryInterface $member, array $answers): bool;

    public function pin(): bool;

    public function unpin(): bool;
}
