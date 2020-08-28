<?php

declare(strict_types=1);

namespace bizley\podium\api\ars;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Message Active Record.
 *
 * @property int                      $id
 * @property int|null                 $reply_to_id
 * @property string                   $subject
 * @property string                   $content
 * @property int                      $created_at
 * @property int                      $updated_at
 * @property MessageActiveRecord|null $replyTo
 */
class MessageActiveRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%podium_message}}';
    }

    public function behaviors(): array
    {
        return ['timestamp' => TimestampBehavior::class];
    }

    public function rules(): array
    {
        return [
            [['subject', 'content'], 'required'],
            [['subject', 'content'], 'string', 'min' => 3],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'content' => Yii::t('podium.label', 'message.content'),
            'subject' => Yii::t('podium.label', 'message.subject'),
        ];
    }

    public function getReplyTo(): ActiveQuery
    {
        return $this->hasOne(__CLASS__, ['id' => 'reply_to_id']);
    }
}
