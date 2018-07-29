<?php

declare(strict_types=1);

namespace bizley\podium\api\models\member;

use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\repos\MemberRepo;

/**
 * Class Member
 * @package bizley\podium\api\models
 */
class Member extends MemberRepo implements MembershipInterface
{
    use ModelTrait;

    /**
     * @param int|string $userId
     * @return MembershipInterface|null
     */
    public static function findByUserId($userId): ?MembershipInterface
    {
        return static::findOne(['user_id' => $userId]);
    }
}
