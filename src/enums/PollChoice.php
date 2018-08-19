<?php

declare(strict_types=1);

namespace bizley\podium\api\enums;

use Yii;

/**
 * Class PollChoice
 * @package bizley\podium\api\enums
 */
final class PollChoice extends BaseEnum
{
    public const SINGLE = 'single';
    public const MULTIPLE = 'multiple';

    /**
     * {@inheritdoc}
     */
    public static function data(): array
    {
        return [
            self::SINGLE => Yii::t('podium.enum', 'poll.choice.single'),
            self::MULTIPLE => Yii::t('podium.enum', 'poll.choice.multiple'),
        ];
    }
}
