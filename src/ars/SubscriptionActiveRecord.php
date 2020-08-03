<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Subscription Active Record.
 *
 * @property int $member_id
 * @property int $thread_id
 * @property bool $seen
 * @property int $created_at
 * @property int $updated_at
 */
class SubscriptionActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_subscription}}';
    }

    public function behaviors(): array
    {
        return ['timestamp' => TimestampBehavior::class];
    }
}
