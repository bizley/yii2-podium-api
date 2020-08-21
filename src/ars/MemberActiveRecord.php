<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Member Active Record.
 *
 * @property int    $id
 * @property string $user_id
 * @property string $username
 * @property string $slug
 * @property string $status_id
 * @property int    $created_at
 * @property int    $updated_at
 */
class MemberActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_member}}';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => TimestampBehavior::class,
            'slug' => [
                'class' => SluggableBehavior::class,
                'attribute' => 'username',
                'ensureUnique' => true,
                'immutable' => true,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['username'], 'required'],
            [['username', 'slug'], 'string', 'max' => 191],
            [['username'], 'unique'],
            [['slug'], 'match', 'pattern' => '/^[a-zA-Z0-9\-]{0,191}$/'],
            [['slug'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'username' => Yii::t('podium.label', 'member.username'),
            'slug' => Yii::t('podium.label', 'member.slug'),
        ];
    }
}
