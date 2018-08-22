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
     * Returns poll form handler.
     * @return CategorisedFormInterface
     */
    public function getPollForm(): CategorisedFormInterface;

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
     * @param ModelFormInterface $postPollForm
     * @param array $data
     * @return PodiumResponse
     */
    public function edit(ModelFormInterface $postPollForm, array $data): PodiumResponse;

    /**
     * @param RemovableInterface $pollRemover
     * @return PodiumResponse
     */
    public function remove(RemovableInterface $pollRemover): PodiumResponse;

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
