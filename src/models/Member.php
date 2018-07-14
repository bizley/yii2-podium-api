<?php

declare(strict_types=1);

namespace bizley\podium\api\models;

use bizley\podium\api\interfaces\MemberInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\repos\MemberRepo;

/**
 * Class Membership
 * @package bizley\podium\api\models
 */
class Member extends MemberRepo implements MembershipInterface, MemberInterface
{
    /**
     * @param int|string $userId
     * @return MembershipInterface
     */
    public static function findMembership($userId): MembershipInterface
    {
        return static::findOne(['user_id' => $userId]);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
