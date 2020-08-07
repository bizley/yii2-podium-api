<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Message Active Record.
 *
 * @property int $id
 * @property int|null $reply_to_id
 * @property string $subject
 * @property string $content
 * @property int $created_at
 * @property int $updated_at
 *
 * @property MessageActiveRecord|null $replyTo
 */
class MessageActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_message}}';
    }

    public function getReplyTo(): ActiveQuery
    {
        return $this->hasOne(__CLASS__, ['id' => 'reply_to_id']);
    }
}
