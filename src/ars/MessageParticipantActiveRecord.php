<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Message Participant Active Record.
 *
 * @property int                 $message_id
 * @property int                 $member_id
 * @property string              $status_id
 * @property string              $side_id
 * @property bool                $archived
 * @property int                 $created_at
 * @property int                 $updated_at
 * @property MessageActiveRecord $message
 * @property MemberActiveRecord  $member
 */
class MessageParticipantActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_message_participant}}';
    }

    public function behaviors(): array
    {
        return ['timestamp' => TimestampBehavior::class];
    }

    public function getMessage(): ActiveQuery
    {
        return $this->hasOne(MessageActiveRecord::class, ['id' => 'member_id']);
    }

    public function getMember(): ActiveQuery
    {
        return $this->hasOne(MemberActiveRecord::class, ['id' => 'member_id']);
    }
}
