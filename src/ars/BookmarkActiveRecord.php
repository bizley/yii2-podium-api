<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Bookmark Active Record.
 *
 * @property int $member_id
 * @property int $thread_id
 * @property int $last_seen
 * @property int $updated_at
 *
 * @property MemberActiveRecord $member
 * @property ThreadActiveRecord $thread
 */
class BookmarkActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_bookmark}}';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => false,
            ],
        ];
    }

    public function getMember(): ActiveQuery
    {
        return $this->hasOne(MemberActiveRecord::class, ['id' => 'member_id']);
    }

    public function getThread(): ActiveQuery
    {
        return $this->hasOne(ThreadActiveRecord::class, ['id' => 'thread_id']);
    }
}
