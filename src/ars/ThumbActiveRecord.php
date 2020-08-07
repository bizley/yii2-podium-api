<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Thumb Active Record.
 *
 * @property int $member_id
 * @property int $post_id
 * @property int $thumb
 * @property int $created_at
 * @property int $updated_at
 *
 * @property PostActiveRecord $post
 * @property MemberActiveRecord $member
 */
class ThumbActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_thumb}}';
    }

    public function getPost(): ActiveQuery
    {
        return $this->hasOne(PostActiveRecord::class, ['id' => 'post_id']);
    }

    public function getMember(): ActiveQuery
    {
        return $this->hasOne(MemberActiveRecord::class, ['id' => 'member_id']);
    }
}
