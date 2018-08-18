<?php

declare(strict_types=1);

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Thumb Active Record.
 *
 * @property int $id
 * @property string $name
 * @property int $min_posts
 * @property int $created_at
 * @property int $updated_at
 */
class RankRepo extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%podium_rank}}';
    }
}
