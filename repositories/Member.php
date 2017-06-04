<?php

namespace bizley\podium\api\repositories;

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
 * @property string $email
 * @property int $status
 * @property int $role
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
}
