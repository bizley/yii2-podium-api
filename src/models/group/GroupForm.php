<?php

declare(strict_types=1);

namespace bizley\podium\api\models\group;

use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\repos\GroupRepo;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Class GroupForm
 * @package bizley\podium\api\models\group
 */
class GroupForm extends GroupRepo implements ModelFormInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.group.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.group.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.group.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.group.editing.after';

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
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t('podium.label', 'group.name'),
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
    public function beforeCreate(): bool
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_CREATING, $event);

        return $event->canCreate;
    }

    /**
     * @return bool
     */
    public function create(): bool
    {
        if (!$this->beforeCreate()) {
            return false;
        }
        if (!$this->save()) {
            Yii::error(['Error while creating group', $this->errors], 'podium');
            return false;
        }
        $this->afterCreate();
        return true;
    }

    public function afterCreate(): void
    {
        $this->trigger(self::EVENT_AFTER_CREATING, new ModelEvent([
            'model' => $this
        ]));
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
            Yii::error(['Error while editing group', $this->errors], 'podium');
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
}
