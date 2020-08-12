<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Rank Active Record.
 *
 * @property int    $id
 * @property string $name
 * @property int    $min_posts
 * @property int    $created_at
 * @property int    $updated_at
 */
class RankActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_rank}}';
    }

    public function behaviors(): array
    {
        return ['timestamp' => TimestampBehavior::class];
    }

    public function rules(): array
    {
        return [
            [['name', 'min_posts'], 'required'],
            [['name'], 'string', 'max' => 191],
            [['min_posts'], 'integer', 'min' => 0],
            [['min_posts'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t('podium.label', 'rank.name'),
            'min_posts' => Yii::t('podium.label', 'rank.minimum.posts'),
        ];
    }
}
