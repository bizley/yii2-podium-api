<?php

declare(strict_types=1);

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Group Active Record.
 *
 * @property int $id
 * @property string $name
 * @property int $created_at
 * @property int $updated_at
 */
class GroupRepo extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%podium_group}}';
    }
}
