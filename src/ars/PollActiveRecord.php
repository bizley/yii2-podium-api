<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Poll Active Record.
 *
 * @property int                $id
 * @property int                $thread_id
 * @property int                $author_id
 * @property string             $question
 * @property bool               $revealed
 * @property string             $choice_id
 * @property int                $created_at
 * @property int                $updated_at
 * @property int                $expires_at
 * @property ThreadActiveRecord $thread
 * @property MemberActiveRecord $author
 */
class PollActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_poll}}';
    }

    public function getThread(): ActiveQuery
    {
        return $this->hasOne(ThreadActiveRecord::class, ['id' => 'thread_id']);
    }

    public function getAuthor(): ActiveQuery
    {
        return $this->hasOne(MemberActiveRecord::class, ['id' => 'author_id']);
    }
}
