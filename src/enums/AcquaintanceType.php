<?php

namespace bizley\podium\api\enums;

use Yii;

/**
 * Acquaintance Type ENUM
 */
final class AcquaintanceType extends BaseEnum
{
    public const FRIEND = 'friend';
    public const IGNORE = 'ignore';

    /**
     * {@inheritdoc}
     */
    public static function data(): array
    {
        return [
            self::FRIEND => Yii::t('podium.enum', 'acquaintance.type.friend'),
            self::IGNORE => Yii::t('podium.enum', 'acquaintance.type.ignore'),
        ];
    }
}
