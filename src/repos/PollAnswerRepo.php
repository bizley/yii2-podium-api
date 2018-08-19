<?php

declare(strict_types=1);

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Poll Answer Active Record.
 *
 * @property int $id
 * @property int $poll_id
 * @property string $answer
 * @property int $created_at
 * @property int $updated_at
 */
class PollAnswerRepo extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%podium_poll_answer}}';
    }
}
