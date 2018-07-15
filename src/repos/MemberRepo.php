<?php

declare(strict_types=1);

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Member Active Record.
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
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%podium_member}}';
    }
}
