<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface MembershipInterface
 * @package bizley\podium\api\interfaces
 */
interface MembershipInterface extends ModelInterface
{
    /**
     * Finds a membership by the given user ID.
     * @param int|string $userId
     * @return MembershipInterface|null the membership object that matches the given user ID
     */
    public static function findByUserId($userId): ?MembershipInterface;

    /**
     * @return string
     */
    public function getUsername(): string;
}
