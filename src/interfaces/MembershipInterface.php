<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface MembershipInterface
 * @package bizley\podium\api\interfaces
 */
interface MembershipInterface
{
    /**
     * @param int $memberId
     * @return MembershipInterface|null
     */
    public static function findMemberById(int $memberId): ?MembershipInterface;

    /**
     * Finds a membership by the given user ID.
     * @param int|string $userId
     * @return MembershipInterface|null the membership object that matches the given user ID
     */
    public static function findMemberByUserId($userId): ?MembershipInterface;

    /**
     * @return int
     */
    public function getId(): int;
}
