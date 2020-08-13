<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Poll Vote Active Record.
 *
 * @property int                    $poll_id
 * @property int                    $answer_id
 * @property int                    $member_id
 * @property int                    $created_at
 * @property PollActiveRecord       $poll
 * @property PollAnswerActiveRecord $answer
 * @property MemberActiveRecord     $member
 */
class PollVoteActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_poll_vote}}';
    }

    public function getPoll(): ActiveQuery
    {
        return $this->hasOne(PollActiveRecord::class, ['id' => 'poll_id']);
    }

    public function getAnswer(): ActiveQuery
    {
        return $this->hasOne(PollAnswerActiveRecord::class, ['id' => 'answer_id']);
    }

    public function getMember(): ActiveQuery
    {
        return $this->hasOne(MemberActiveRecord::class, ['id' => 'member_id']);
    }
}
