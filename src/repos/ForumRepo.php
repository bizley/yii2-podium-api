<?php

declare(strict_types=1);

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Forum Active Record.
 *
 * @property int $id
 * @property int $author_id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property bool $visible
 * @property int $sort
 * @property int $threads_count
 * @property int $posts_count
 * @property int $created_at
 * @property int $updated_at
 */
class ForumRepo extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%podium_forum}}';
    }
}
