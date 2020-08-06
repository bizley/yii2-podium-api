<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\db\ActiveRecord;

/**
 * Member Active Record.
 *
 * @property int $id
 * @property string $user_id
 * @property string $username
 * @property string $slug
 * @property string $status_id
 * @property int $created_at
 * @property int $updated_at
 */
class MemberActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_member}}';
    }
}
