<?php

declare(strict_types=1);

namespace bizley\podium\api\enums;

use Yii;

/**
 * Class AcquaintanceType
 * @package bizley\podium\api\enums
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
