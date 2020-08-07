<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Acquaintance Active Record.
 *
 * @property int $member_id
 * @property int $target_id
 * @property string $type_id
 * @property int $created_at
 *
 * @property MemberActiveRecord $member
 * @property MemberActiveRecord $target
 */
class AcquaintanceActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_acquaintance}}';
    }

    public function getMember(): ActiveQuery
    {
        return $this->hasOne(MemberActiveRecord::class, ['id' => 'member_id']);
    }

    public function getTarget(): ActiveQuery
    {
        return $this->hasOne(MemberActiveRecord::class, ['id' => 'target_id']);
    }
}
