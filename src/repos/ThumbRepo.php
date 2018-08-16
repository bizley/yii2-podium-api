<?php

declare(strict_types=1);

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Thumb Active Record.
 *
 * @property int $member_id
 * @property int $post_id
 * @property int $thumb
 * @property int $created_at
 * @property int $updated_at
 */
class ThumbRepo extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%podium_thumb}}';
    }
}
