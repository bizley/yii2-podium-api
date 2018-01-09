<?php

namespace bizley\podium\api\repositories;

use bizley\podium\api\dictionaries\Permission as PermissionType;
use yii\behaviors\TimestampBehavior;

/**
 * Permission Repository.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 *
 * @property int $member_id
 * @property int $permission
 * @property int $created_at
 */
class Permission extends Repository
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_permission}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'permission'], 'required'],
            [['member_id'], 'exist', 'targetClass' => Member::class, 'targetAttribute' => 'id'],
            ['permission', 'in', 'range' => PermissionType::range()],
            [['member_id', 'permission'], 'unique', 'targetAttribute' => ['member_id', 'permission']],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::class, ['id' => 'member_id']);
    }
}
