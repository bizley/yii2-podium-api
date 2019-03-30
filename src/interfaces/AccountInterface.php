<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface AccountInterface
 * @package bizley\podium\api\interfaces
 */
interface AccountInterface
{
    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Makes target a friend.
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function befriendMember(MembershipInterface $target): PodiumResponse;

    /**
     * Makes target a friend no more.
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function unfriendMember(MembershipInterface $target): PodiumResponse;

    /**
     * Sets target as ignored.
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function ignoreMember(MembershipInterface $target): PodiumResponse;

    /**
     * Sets target as unignored.
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function unignoreMember(MembershipInterface $target): PodiumResponse;

    /**
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function thumbUpPost(ModelInterface $post): PodiumResponse;

    /**
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function thumbDownPost(ModelInterface $post): PodiumResponse;

    /**
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function thumbResetPost(ModelInterface $post): PodiumResponse;

    /**
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function subscribeThread(ModelInterface $thread): PodiumResponse;

    /**
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function unsubscribeThread(ModelInterface $thread): PodiumResponse;

    /**
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function markPost(ModelInterface $post): PodiumResponse;

    /**
     * @param ModelInterface $group
     * @return PodiumResponse
     */
    public function joinGroup(ModelInterface $group): PodiumResponse;

    /**
     * @param ModelInterface $group
     * @return PodiumResponse
     */
    public function leaveGroup(ModelInterface $group): PodiumResponse;

    /**
     * Votes in poll.
     * @param PollModelInterface $poll
     * @param PollAnswerModelInterface[] $answers
     * @return PodiumResponse
     */
    public function votePoll(PollModelInterface $poll, array $answers): PodiumResponse;
}
