<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface IgnoringInterface
 * @package bizley\podium\api\interfaces
 */
interface IgnoringInterface
{
    /**
     * Initiator of ignoring.
     * @param MemberModelInterface $member
     */
    public function setMember(MemberModelInterface $member): void;

    /**
     * Target of ignoring.
     * @param MemberModelInterface $target
     */
    public function setTarget(MemberModelInterface $target): void;

    /**
     * Handles ignoring process.
     * @return bool whether ignoring was successful
     */
    public function ignore(): bool;

    /**
     * Handles unignoring process.
     * @return bool whether unignoring was successful
     */
    public function unignore(): bool;

    /**
     * @return bool whether member is ignoring target
     */
    public function isIgnoring(): bool;
}
