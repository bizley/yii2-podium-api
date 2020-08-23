<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

/**
 * Interface PollInterface.
 */
interface PollInterface
{
    /**
     * Creates poll post.
     */
    public function create(
        MemberRepositoryInterface $author,
        ThreadRepositoryInterface $thread,
        array $answers,
        array $data = []
    ): PodiumResponse;

    /**
     * Updates poll post.
     */
    public function edit(PollRepositoryInterface $poll, array $answers, array $data = []): PodiumResponse;

    public function remove(PollRepositoryInterface $poll): PodiumResponse;

    /**
     * Votes in poll.
     */
    public function vote(
        PollRepositoryInterface $poll,
        MemberRepositoryInterface $member,
        array $answers
    ): PodiumResponse;

    /**
     * Moves poll to different thread.
     */
    public function move(PollRepositoryInterface $poll, ThreadRepositoryInterface $thread): PodiumResponse;

    public function archive(PollRepositoryInterface $poll): PodiumResponse;

    public function revive(PollRepositoryInterface $poll): PodiumResponse;
}
