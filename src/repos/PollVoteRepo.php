<?php

declare(strict_types=1);

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Poll Vote Active Record.
 *
 * @property int $poll_id
 * @property int $answer_id
 * @property int $member_id
 * @property int $created_at
 */
class PollVoteRepo extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%podium_poll_vote}}';
    }
}
