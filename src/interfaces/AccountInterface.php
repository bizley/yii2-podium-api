<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

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
     * @return bool
     */
    public function befriend(MembershipInterface $target): bool;

    /**
     * Makes target a friend no more.
     * @param MembershipInterface $target
     * @return bool
     */
    public function unfriend(MembershipInterface $target): bool;

    /**
     * Sets target as ignored.
     * @param MembershipInterface $target
     * @return bool
     */
    public function ignore(MembershipInterface $target): bool;

    /**
     * Sets target as unignored.
     * @param MembershipInterface $target
     * @return bool
     */
    public function unignore(MembershipInterface $target): bool;

    /**
     * @param ModelInterface $post
     * @return bool
     */
    public function thumbUp(ModelInterface $post): bool;

    /**
     * @param ModelInterface $post
     * @return bool
     */
    public function thumbDown(ModelInterface $post): bool;

    /**
     * @param ModelInterface $post
     * @return bool
     */
    public function thumbReset(ModelInterface $post): bool;

    /**
     * @param ModelInterface $thread
     * @return bool
     */
    public function subscribe(ModelInterface $thread): bool;

    /**
     * @param ModelInterface $thread
     * @return bool
     */
    public function unsubscribe(ModelInterface $thread): bool;

    /**
     * @param ModelInterface $post
     * @return bool
     */
    public function mark(ModelInterface $post): bool;

    /**
     * @param ModelInterface $group
     * @return bool
     */
    public function join(ModelInterface $group): bool;

    /**
     * @param ModelInterface $group
     * @return bool
     */
    public function leave(ModelInterface $group): bool;

    /**
     * Votes in poll.
     * @param PollModelInterface $poll
     * @param PollAnswerModelInterface[] $answers
     * @return bool
     */
    public function vote(PollModelInterface $poll, array $answers): bool;
}
