<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface PollInterface
 * @package bizley\podium\api\interfaces
 */
interface PollInterface
{
    /**
     * @param int $id
     * @return PollModelInterface|null
     */
    public function getPollByPostId(int $id): ?PollModelInterface;

    /**
     * Returns poll form handler instance.
     * @param int|null $id
     * @return CategorisedFormInterface|null
     */
    public function getPollForm(?int $id = null): ?CategorisedFormInterface;

    /**
     * Creates poll post.
     * @param array $data
     * @param MembershipInterface $author
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function create(array $data, MembershipInterface $author, ModelInterface $thread): PodiumResponse;

    /**
     * Updates poll post.
     * @param array $data
     * @return PodiumResponse
     */
    public function edit(array $data): PodiumResponse;

    /**
     * @param RemoverInterface $pollRemover
     * @return PodiumResponse
     */
    public function remove(RemoverInterface $pollRemover): PodiumResponse;

    /**
     * Returns voting handler.
     * @return VotingInterface
     */
    public function getVoting(): VotingInterface;

    /**
     * Votes in poll.
     * @param MembershipInterface $member
     * @param PollModelInterface $poll
     * @param PollAnswerModelInterface[] $answers
     * @return PodiumResponse
     */
    public function vote(MembershipInterface $member, PollModelInterface $poll, array $answers): PodiumResponse;
}
