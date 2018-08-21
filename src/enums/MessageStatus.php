<?php

declare(strict_types=1);

namespace bizley\podium\api\enums;

use Yii;

/**
 * Class MessageStatus
 * @package bizley\podium\api\enums
 */
final class MessageStatus extends BaseEnum
{
    public const NEW = 'new';
    public const READ = 'read';
    public const REPLIED = 'replied';

    /**
     * {@inheritdoc}
     */
    public static function data(): array
    {
        return [
            self::NEW => Yii::t('podium.enum', 'message.status.new'),
            self::READ => Yii::t('podium.enum', 'message.status.read'),
            self::REPLIED => Yii::t('podium.enum', 'message.status.replied'),
        ];
    }
}
