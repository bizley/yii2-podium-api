<?php

declare(strict_types=1);

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Thread Active Record.
 *
 * @property int $id
 * @property int $member_id
 * @property int $thread_id
 * @property bool $seen
 * @property int $created_at
 * @property int $updated_at
 */
class SubscriptionRepo extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%podium_subscription}}';
    }
}
