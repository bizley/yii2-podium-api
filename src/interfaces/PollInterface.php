<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

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
    public function getById(int $id): ?PollModelInterface;

    /**
     * @param int $threadId
     * @return PollModelInterface|null
     */
    public function getByThreadId(int $threadId): ?PollModelInterface;

    /**
     * Returns poll form handler instance.
     * @param int|null $id
     * @return CategorisedFormInterface|null
     */
    public function getForm(int $id = null): ?CategorisedFormInterface;

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
     * @param int $id
     * @return RemoverInterface|null
     */
    public function getRemover(int $id): ?RemoverInterface;

    /**
     * @param int $id
     * @return PodiumResponse
     */
    public function remove(int $id): PodiumResponse;

    /**
     * Returns voting handler.
     * @return VoterInterface
     */
    public function getVoter(): VoterInterface;

    /**
     * Votes in poll.
     * @param MembershipInterface $member
     * @param PollModelInterface $poll
     * @param PollAnswerModelInterface[] $answers
     * @return PodiumResponse
     */
    public function vote(MembershipInterface $member, PollModelInterface $poll, array $answers): PodiumResponse;
}
