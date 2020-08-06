<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\db\ActiveRecord;

/**
 * Acquaintance Active Record.
 *
 * @property int $member_id
 * @property int $target_id
 * @property string $type_id
 * @property int $created_at
 */
class AcquaintanceActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_acquaintance}}';
    }
}
