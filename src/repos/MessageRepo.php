<?php

declare(strict_types=1);

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Message Active Record.
 *
 * @property int $id
 * @property int $reply_to_id
 * @property string $subject
 * @property string $content
 * @property int $created_at
 * @property int $updated_at
 */
class MessageRepo extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%podium_message}}';
    }
}
