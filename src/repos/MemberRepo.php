<?php

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Member Active Record.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 *
 * @property int $id
 * @property string $user_id
 * @property string $username
 * @property string $status_id
 * @property int $created_at
 * @property int $updated_at
 */
class MemberRepo extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%podium_member}}';
    }
}
