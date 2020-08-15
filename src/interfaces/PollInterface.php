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
        array $data,
        array $answers,
        MemberRepositoryInterface $author,
        ThreadRepositoryInterface $thread
    ): PodiumResponse;

    /**
     * Updates poll post.
     */
    public function edit($id, array $data, array $answers): PodiumResponse;

    public function remove($id): PodiumResponse;

    /**
     * Votes in poll.
     */
    public function vote(
        MemberRepositoryInterface $member,
        PollRepositoryInterface $poll,
        array $answers
    ): PodiumResponse;
}
