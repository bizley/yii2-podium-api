<?php

declare(strict_types=1);

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Poll Active Record.
 *
 * @property int $id
 * @property int $post_id
 * @property string $question
 * @property bool $revealed
 * @property string $choice_id
 * @property int $created_at
 * @property int $updated_at
 * @property int $expires_at
 */
class PollRepo extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%podium_poll}}';
    }
}
