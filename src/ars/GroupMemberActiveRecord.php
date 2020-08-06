<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\db\ActiveRecord;

/**
 * Group Member Active Record.
 *
 * @property int $member_id
 * @property int $group_id
 * @property int $created_at
 */
class GroupMemberActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_group_member}}';
    }
}
