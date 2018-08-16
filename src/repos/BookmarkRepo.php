<?php

declare(strict_types=1);

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Subscription Active Record.
 *
 * @property int $member_id
 * @property int $thread_id
 * @property int $last_seen
 * @property int $updated_at
 */
class BookmarkRepo extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%podium_bookmark}}';
    }
}
