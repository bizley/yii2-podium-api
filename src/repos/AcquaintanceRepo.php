<?php

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Acquaintance Active Record.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 *
 * @property int $id
 * @property int $member_id
 * @property int $target_id
 * @property string $type_id
 * @property int $created_at
 */
class AcquaintanceRepo extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%podium_acquaintance}}';
    }
}
