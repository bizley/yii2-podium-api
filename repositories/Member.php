<?php

namespace bizley\podium\api\repositories;

use bizley\podium\api\dictionaries\Status;
use yii\db\ActiveRecord;

/**
 * Member Active Record.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.10.0
 *
 * @property int $id
 * @property string $username
 * @property string $slug
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class Member extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_member}}';
    }

    public function rules()
    {
        return [
            ['status', 'default', 'value' => Status::REGISTERED],
            ['username', 'required'],
            ['status', 'in', 'range' => Status::range()],
        ];
    }
}
