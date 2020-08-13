<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\db\ActiveRecord;

/**
 * Group Active Record.
 *
 * @property int    $id
 * @property string $name
 * @property int    $created_at
 * @property int    $updated_at
 */
class GroupActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_group}}';
    }
}
