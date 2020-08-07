<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Poll Answer Active Record.
 *
 * @property int $id
 * @property int $poll_id
 * @property string $answer
 * @property int $created_at
 * @property int $updated_at
 *
 * @property PollActiveRecord $poll
 */
class PollAnswerActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_poll_answer}}';
    }

    public function getPoll(): ActiveQuery
    {
        return $this->hasOne(PollActiveRecord::class, ['id' => 'poll_id']);
    }
}
