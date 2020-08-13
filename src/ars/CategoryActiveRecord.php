<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Category Active Record.
 *
 * @property int                $id
 * @property int                $author_id
 * @property string             $name
 * @property string             $slug
 * @property string             $description
 * @property bool               $visible
 * @property int                $sort
 * @property int                $created_at
 * @property int                $updated_at
 * @property bool               $archived
 * @property MemberActiveRecord $author
 */
class CategoryActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_category}}';
    }

    public function getAuthor(): ActiveQuery
    {
        return $this->hasOne(MemberActiveRecord::class, ['id' => 'author_id']);
    }
}
