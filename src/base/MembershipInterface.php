<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

/**
 * Interface MembershipInterface
 * @package bizley\podium\api\models
 */
interface MembershipInterface
{
    /**
     * Finds a membership by the given user ID.
     * @param int|string $userId
     * @return MembershipInterface the membership object that matches the given user ID
     */
    public static function findMembership($userId): MembershipInterface;

    /**
     * Returns an ID that can uniquely identify a member membership.
     * @return int
     */
    public function getId(): int;
}
