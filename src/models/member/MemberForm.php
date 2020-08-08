<?php

declare(strict_types=1);

namespace bizley\podium\api\models\member;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\interfaces\ModelFormInterface;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * Class MemberForm
 * @package bizley\podium\api\models\member
 */
class MemberForm extends Member implements ModelFormInterface
{
    public const EVENT_BEFORE_EDITING = 'podium.member.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.member.editing.after';

    /**
     * @return array
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => TimestampBehavior::class,
            'slug' => [
                'class' => SluggableBehavior::class,
                'attribute' => 'username',
                'ensureUnique' => true,
                'immutable' => true,
            ],
        ];
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['username'], 'required'],
            [['username', 'slug'], 'string', 'max' => 255],
            [['username'], 'unique'],
            [['slug'], 'match', 'pattern' => '/^[a-zA-Z0-9\-]{0,255}$/'],
            [['slug'], 'unique'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'username' => Yii::t('podium.label', 'member.username'),
            'slug' => Yii::t('podium.label', 'member.slug'),
        ];
    }

    /**
     * @param array $data
     * @return bool
     */
    public function loadData(array $data = []): bool
    {
        return $this->load($data, '');
    }

    /**
     * @return bool
     */
    public function beforeEdit(): bool
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_EDITING, $event);

        return $event->canEdit;
    }

    /**
     * @return PodiumResponse
     */
    public function edit(): PodiumResponse
    {
        if (!$this->beforeEdit()) {
            return PodiumResponse::error();
        }

        if (!$this->save()) {
            Yii::error(['Error while editing member', $this->errors], 'podium');

            return PodiumResponse::error($this);
        }

        $this->afterEdit();

        return PodiumResponse::success($this->getOldAttributes());
    }

    public function afterEdit(): void
    {
        $this->trigger(self::EVENT_AFTER_EDITING, new ModelEvent(['model' => $this]));
    }

    /**
     * Creates new model.
     * @return PodiumResponse
     * @throws NotSupportedException
     */
    public function create(): PodiumResponse
    {
        throw new NotSupportedException(
            'Member must not be created using this form. Use bizley\podium\api\models\member\MemberRegisterer.'
        );
    }
}
