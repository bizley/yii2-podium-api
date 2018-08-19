<?php

declare(strict_types=1);

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Group Member Active Record.
 *
 * @property int $member_id
 * @property int $group_id
 * @property int $created_at
 */
class GroupMemberRepo extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%podium_group_member}}';
    }
}
