<?php

declare(strict_types=1);

namespace bizley\podium\api\enums;

use Yii;

/**
 * Class PostType
 * @package bizley\podium\api\enums
 */
final class PostType extends BaseEnum
{
    public const POST = 'post';
    public const POLL = 'poll';

    /**
     * {@inheritdoc}
     */
    public static function data(): array
    {
        return [
            self::POST => Yii::t('podium.enum', 'post.type.post'),
            self::POLL => Yii::t('podium.enum', 'post.type.poll'),
        ];
    }
}
