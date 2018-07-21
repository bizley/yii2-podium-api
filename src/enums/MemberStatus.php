<?php

declare(strict_types=1);

namespace bizley\podium\api\enums;

use Yii;

/**
 * Class MemberStatus
 * @package bizley\podium\api\enums
 */
final class MemberStatus extends BaseEnum
{
    public const REGISTERED = 'registered';
    public const ACTIVE = 'active';
    public const BANNED = 'banned';

    /**
     * {@inheritdoc}
     */
    public static function data(): array
    {
        return [
            self::REGISTERED => Yii::t('podium.enum', 'member.status.registered'),
            self::ACTIVE => Yii::t('podium.enum', 'member.status.active'),
            self::BANNED => Yii::t('podium.enum', 'member.status.banned'),
        ];
    }
}
