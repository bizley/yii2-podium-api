<?php

declare(strict_types=1);

namespace bizley\podium\api\repos;

use yii\db\ActiveRecord;

/**
 * Poll Active Record.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property bool $visible
 * @property int $sort
 * @property int $created_at
 * @property int $updated_at
 */
class PollRepo extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%podium_category}}';
    }
}
