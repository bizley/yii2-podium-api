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
    public function befriend(MembershipInterface $target): PodiumResponse;

    /**
     * Makes target a friend no more.
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function unfriend(MembershipInterface $target): PodiumResponse;

    /**
     * Sets target as ignored.
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function ignore(MembershipInterface $target): PodiumResponse;

    /**
     * Sets target as unignored.
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function unignore(MembershipInterface $target): PodiumResponse;

    /**
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function thumbUp(ModelInterface $post): PodiumResponse;

    /**
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function thumbDown(ModelInterface $post): PodiumResponse;

    /**
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function thumbReset(ModelInterface $post): PodiumResponse;

    /**
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function subscribe(ModelInterface $thread): PodiumResponse;

    /**
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function unsubscribe(ModelInterface $thread): PodiumResponse;

    /**
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function mark(ModelInterface $post): PodiumResponse;

    /**
     * @param ModelInterface $group
     * @return PodiumResponse
     */
    public function join(ModelInterface $group): PodiumResponse;

    /**
     * @param ModelInterface $group
     * @return PodiumResponse
     */
    public function leave(ModelInterface $group): PodiumResponse;

    /**
     * Votes in poll.
     * @param PollModelInterface $poll
     * @param PollAnswerModelInterface[] $answers
     * @return PodiumResponse
     */
    public function vote(PollModelInterface $poll, array $answers): PodiumResponse;
}
