<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\db\ActiveRecord;

/**
 * Rank Active Record.
 *
 * @property int    $id
 * @property string $name
 * @property int    $min_posts
 * @property int    $created_at
 * @property int    $updated_at
 */
class RankActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_rank}}';
    }
}
