<?php

declare(strict_types=1);

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Message Active Record.
 *
 * @property int $message_id
 * @property int $member_id
 * @property string $status_id
 * @property string $side_id
 * @property bool $archived
 * @property int $created_at
 * @property int $updated_at
 */
class MessageParticipantRepo extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%podium_message_participant}}';
    }
}
