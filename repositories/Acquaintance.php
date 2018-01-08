<?php

namespace bizley\podium\api\repositories;

use bizley\podium\api\dictionaries\Acquaintance as AcquaintanceType;
use yii\behaviors\TimestampBehavior;

/**
 * Acquaintance Active Record.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 *
 * @property int $id
 * @property int $member_id
 * @property int $target_id
 * @property int $type
 * @property int $created_at
 *
 * @property Member $member
 * @property Member $target
 */
class Acquaintance extends Repository
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_acquaintance}}';
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
            [['member_id', 'target_id', 'type'], 'required'],
            [['member_id', 'target_id'], 'exist', 'targetClass' => Member::class, 'targetAttribute' => 'id'],
            ['type', 'in', 'range' => AcquaintanceType::range()],
            [['member_id', 'target_id', 'type'], 'unique', 'targetAttribute' => ['member_id', 'target_id', 'type']],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::class, ['id' => 'member_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTarget()
    {
        return $this->hasOne(Member::class, ['id' => 'target_id']);
    }
}
