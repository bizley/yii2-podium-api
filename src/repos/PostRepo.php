<?php

declare(strict_types=1);

namespace bizley\podium\api\repos;

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
 */
class PostRepo extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%podium_post}}';
    }
}
