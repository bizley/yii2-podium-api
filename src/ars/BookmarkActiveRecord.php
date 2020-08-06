<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\db\ActiveRecord;

/**
 * Bookmark Active Record.
 *
 * @property int $member_id
 * @property int $thread_id
 * @property int $last_seen
 * @property int $updated_at
 */
class BookmarkActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_bookmark}}';
    }
}
