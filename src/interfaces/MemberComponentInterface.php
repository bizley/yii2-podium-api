<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface MemberComponentInterface
 * @package bizley\podium\api\interfaces
 */
interface MemberComponentInterface
{
    /**
     * Returns registration handler.
     * @return RegistrationInterface
     */
    public function getRegistration(): RegistrationInterface;

    /**
     * Registers account.
     * @param array $data
     * @return bool
     */
    public function register(array $data): bool;

    /**
     * Returns friendship handler.
     * @return FriendshipInterface
     */
    public function getFriendship(): FriendshipInterface;

    /**
     * Makes target friend of member.
     * @param MemberModelInterface $member
     * @param MemberModelInterface $target
     * @return bool
     */
    public function befriend(MemberModelInterface $member, MemberModelInterface $target): bool;

    /**
     * Makes target member friend no more.
     * @param MemberModelInterface $member
     * @param MemberModelInterface $target
     * @return bool
     */
    public function unfriend(MemberModelInterface $member, MemberModelInterface $target): bool;

    /**
     * Returns ignoring handler.
     * @return IgnoringInterface
     */
    public function getIgnoring(): IgnoringInterface;

    /**
     * Sets target as ignored by member.
     * @param MemberModelInterface $member
     * @param MemberModelInterface $target
     * @return bool
     */
    public function ignore(MemberModelInterface $member, MemberModelInterface $target): bool;

    /**
     * Sets target as unignored by member.
     * @param MemberModelInterface $member
     * @param MemberModelInterface $target
     * @return bool
     */
    public function unignore(MemberModelInterface $member, MemberModelInterface $target): bool;
}
