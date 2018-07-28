<?php

declare(strict_types=1);

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Thread Active Record.
 *
 * @property int $id
 * @property int $author_id
 * @property int $category_id
 * @property int $forum_id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property bool $pinned
 * @property bool $locked
 * @property int $posts_count
 * @property int $views_count
 * @property int $created_post_at
 * @property int $updated_post_at
 * @property int $created_at
 * @property int $updated_at
 */
class ThreadRepo extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%podium_thread}}';
    }
}
