<?php

declare(strict_types=1);

namespace bizley\podium\api\enums;

use Yii;

/**
 * Class MessageSide
 * @package bizley\podium\api\enums
 */
final class MessageSide extends BaseEnum
{
    public const SENDER = 'sender';
    public const RECEIVER = 'receiver';

    /**
     * {@inheritdoc}
     */
    public static function data(): array
    {
        return [
            self::SENDER => Yii::t('podium.enum', 'message.side.sender'),
            self::RECEIVER => Yii::t('podium.enum', 'message.side.receiver'),
        ];
    }
}
