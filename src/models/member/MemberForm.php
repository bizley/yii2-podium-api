<?php

declare(strict_types=1);

namespace bizley\podium\api\models\member;

use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\repos\MemberRepo;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;

/**
 * Class MemberForm
 * @package bizley\podium\api\models\member
 */
class MemberForm extends MemberRepo implements ModelFormInterface
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
        ];
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['username'], 'required'],
            [['username'], 'string', 'max' => 255],
            [['username'], 'unique'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'username' => Yii::t('podium.label', 'member.username'),
        ];
    }

    /**
     * @param array|null $data
     * @return bool
     */
    public function loadData(?array $data = null): bool
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
     * @return bool
     */
    public function edit(): bool
    {
        if (!$this->beforeEdit()) {
            return false;
        }
        if (!$this->save()) {
            Yii::error(['Error while editing member', $this->errors], 'podium');
            return false;
        }
        $this->afterEdit();
        return true;
    }

    public function afterEdit(): void
    {
        $this->trigger(self::EVENT_AFTER_EDITING, new ModelEvent([
            'model' => $this
        ]));
    }

    /**
     * Creates new model.
     * @return bool
     * @throws NotSupportedException
     */
    public function create(): bool
    {
        throw new NotSupportedException('Member must not be created using this form. Use bizley\podium\api\models\member\Registration.');
    }
}
