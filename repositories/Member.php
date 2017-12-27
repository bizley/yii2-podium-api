<?php

namespace bizley\podium\api\repositories;

use bizley\podium\api\dictionaries\Status;
use bizley\podium\api\PodiumRepository;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * Member Active Record.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 *
 * @property int $id
 * @property string $username
 * @property string $slug
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class Member extends PodiumRepository
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_member}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'timestamp' => TimestampBehavior::class,
            'slug' => [
                'class' => SluggableBehavior::class,
                'attribute' => 'username',
                'ensureUnique' => true,
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => Status::REGISTERED],
            ['username', 'required'],
            ['username', 'unique'],
            ['status', 'in', 'range' => Status::range()],
        ];
    }
}
