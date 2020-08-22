<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Group Active Record.
 *
 * @property int                       $id
 * @property string                    $name
 * @property int                       $created_at
 * @property int                       $updated_at
 * @property GroupMemberActiveRecord[] $groupMembers
 */
class GroupActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_group}}';
    }

    public function behaviors(): array
    {
        return ['timestamp' => TimestampBehavior::class];
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 191],
            [['name'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return ['name' => Yii::t('podium.label', 'group.name')];
    }

    public function getGroupMembers(): ActiveQuery
    {
        return $this->hasMany(GroupMemberActiveRecord::class, ['group_id' => 'id']);
    }
}
