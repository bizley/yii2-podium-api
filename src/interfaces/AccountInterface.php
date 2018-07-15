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
     * @param MemberModelInterface $target
     * @return bool
     */
    public function befriend(MemberModelInterface $target): bool;

    /**
     * Makes target a friend no more.
     * @param MemberModelInterface $target
     * @return bool
     */
    public function unfriend(MemberModelInterface $target): bool;

    /**
     * Sets target as ignored.
     * @param MemberModelInterface $target
     * @return bool
     */
    public function ignore(MemberModelInterface $target): bool;

    /**
     * Sets target as unignored.
     * @param MemberModelInterface $target
     * @return bool
     */
    public function unignore(MemberModelInterface $target): bool;
}
