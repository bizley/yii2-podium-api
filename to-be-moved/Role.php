<?php

declare(strict_types=1);

namespace bizley\podium\api\enums;

use Yii;

/**
 * Class Role
 * @package bizley\podium\api\enums
 *
 * TODO: move to Podium client
 */
final class Role extends BaseEnum
{
    public const GUEST = 'guest';
    public const MEMBER = 'member';
    public const MODERATOR = 'moderator';
    public const ADMIN = 'admin';

    /**
     * {@inheritdoc}
     */
    public static function data(): array
    {
        return [
            self::GUEST => Yii::t('podium.enum', 'role.guest'),
            self::MEMBER => Yii::t('podium.enum', 'role.member'),
            self::MODERATOR => Yii::t('podium.enum', 'role.moderator'),
            self::ADMIN => Yii::t('podium.enum', 'role.admin'),
        ];
    }
}
