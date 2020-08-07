<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Post Active Record.
 *
 * @property int $id
 * @property int $author_id
 * @property int $category_id
 * @property int $forum_id
 * @property int $thread_id
 * @property string $content
 * @property bool $edited
 * @property int $likes
 * @property int $dislikes
 * @property int $created_at
 * @property int $updated_at
 * @property int $edited_at
 * @property bool $archived
 *
 * @property CategoryActiveRecord $category
 * @property ForumActiveRecord $forum
 * @property ThreadActiveRecord $thread
 * @property MemberActiveRecord $author
 */
class PostActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_post}}';
    }

    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(CategoryActiveRecord::class, ['id' => 'category_id']);
    }

    public function getForum(): ActiveQuery
    {
        return $this->hasOne(ForumActiveRecord::class, ['id' => 'forum_id']);
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
