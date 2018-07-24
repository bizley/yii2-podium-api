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
}
