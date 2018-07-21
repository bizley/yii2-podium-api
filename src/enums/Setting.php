<?php

declare(strict_types=1);

namespace bizley\podium\api\enums;

use Yii;

/**
 * Class Setting
 * @package bizley\podium\api\enums
 */
final class Setting extends BaseEnum
{
    public const POLLS_ALLOWED = 'polls.allowed';
    public const MIN_POSTS_FOR_HOT = 'min.posts.for.hot';
    public const MAINTENANCE_MODE = 'maintenance.mode';
    public const MEMBERS_VISIBLE = 'members.visible';
    public const MERGE_POSTS = 'merge.posts';
    public const NAME = 'name';
    public const REGISTRATION_ALLOWED = 'registration.allowed';

    /**
     * {@inheritdoc}
     */
    public static function data(): array
    {
        return [
            self::POLLS_ALLOWED => Yii::t('podium.enum', 'setting.polls.allowed'),
            self::MIN_POSTS_FOR_HOT => Yii::t('podium.enum', 'setting.minimum.posts.for.hot'),
            self::MAINTENANCE_MODE => Yii::t('podium.enum', 'setting.maintenance.mode'),
            self::MEMBERS_VISIBLE => Yii::t('podium.enum', 'setting.members.visible'),
            self::MERGE_POSTS => Yii::t('podium.enum', 'setting.merge.posts'),
            self::NAME => Yii::t('podium.enum', 'setting.podium.name'),
            self::REGISTRATION_ALLOWED => Yii::t('podium.enum', 'setting.registration.allowed'),
        ];
    }
}
