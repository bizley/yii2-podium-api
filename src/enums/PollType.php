<?php

declare(strict_types=1);

namespace bizley\podium\api\enums;

use Yii;

/**
 * Class PollType
 * @package bizley\podium\api\enums
 */
final class PollType extends BaseEnum
{
    public const SINGLE_CHOICE = 'single';
    public const MULTIPLE_CHOICE = 'multiple';

    /**
     * {@inheritdoc}
     */
    public static function data(): array
    {
        return [
            self::SINGLE_CHOICE => Yii::t('podium.enum', 'poll.type.single'),
            self::MULTIPLE_CHOICE => Yii::t('podium.enum', 'poll.type.multiple'),
        ];
    }
}
