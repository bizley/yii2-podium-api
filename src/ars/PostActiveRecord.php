<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Post Active Record.
 *
 * @property int                $id
 * @property int                $author_id
 * @property int                $thread_id
 * @property int|null           $old_thread_id
 * @property string             $content
 * @property bool               $edited
 * @property bool               $pinned
 * @property int                $likes
 * @property int                $dislikes
 * @property int                $created_at
 * @property int                $updated_at
 * @property int                $edited_at
 * @property bool               $archived
 * @property ThreadActiveRecord $thread
 * @property ThreadActiveRecord $oldThread
 * @property MemberActiveRecord $author
 */
class PostActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_post}}';
    }

    public function getThread(): ActiveQuery
    {
        return $this->hasOne(ThreadActiveRecord::class, ['id' => 'thread_id']);
    }

    public function getAuthor(): ActiveQuery
    {
        return $this->hasOne(MemberActiveRecord::class, ['id' => 'author_id']);
    }

    public function getOldThread(): ActiveQuery
    {
        return $this->hasOne(ThreadActiveRecord::class, ['id' => 'old_thread_id']);
    }
}
