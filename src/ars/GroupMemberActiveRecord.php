<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Group Member Active Record.
 *
 * @property int $member_id
 * @property int $group_id
 * @property int $created_at
 *
 * @property GroupActiveRecord $group
 * @property MemberActiveRecord $member
 */
class GroupMemberActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_group_member}}';
    }

    public function getGroup(): ActiveQuery
    {
        return $this->hasOne(GroupActiveRecord::class, ['id' => 'group_id']);
    }

    public function getMember(): ActiveQuery
    {
        return $this->hasOne(MemberActiveRecord::class, ['id' => 'member_id']);
    }
}
