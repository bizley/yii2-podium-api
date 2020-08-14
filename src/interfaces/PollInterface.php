<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

/**
 * Interface PollInterface.
 */
interface PollInterface
{
    public function fetchByThread(ThreadRepositoryInterface $thread): bool;

    /**
     * Creates poll post.
     */
    public function create(
        array $data,
        MemberRepositoryInterface $author,
        ThreadRepositoryInterface $thread
    ): PodiumResponse;

    /**
     * Updates poll post.
     */
    public function edit(array $data): PodiumResponse;

    public function remove(int $id): PodiumResponse;

    /**
     * Votes in poll.
     *
     * @param PollAnswerModelInterface[] $answers
     */
    public function vote(
        MemberRepositoryInterface $member,
        PollRepositoryInterface $poll,
        array $answers
    ): PodiumResponse;
}
