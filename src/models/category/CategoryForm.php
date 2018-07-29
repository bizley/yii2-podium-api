<?php

declare(strict_types=1);

namespace bizley\podium\api\models\category;

use bizley\podium\api\events\CategoryEvent;
use bizley\podium\api\interfaces\CategoryFormInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\repos\CategoryRepo;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * Class CategoryCategoryForm
 * @package bizley\podium\api\models\category
 */
class CategoryForm extends CategoryRepo implements CategoryFormInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.category.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.category.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.category.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.category.editing.after';

    /**
     * @param MembershipInterface $author
     */
    public function setAuthor(MembershipInterface $author): void
    {
        $this->author_id = $author->getId();
    }

    /**
     * @return array
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => TimestampBehavior::class,
            'slug' => [
                'class' => SluggableBehavior::class,
                'attribute' => 'name',
                'ensureUnique' => true,
            ],
        ];
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['name', 'visible', 'sort'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['visible'], 'boolean'],
            [['sort'], 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t('podium.label', 'category.name'),
            'visible' => Yii::t('podium.label', 'category.visible'),
            'sort' => Yii::t('podium.label', 'category.sort'),
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
        $event = new CategoryEvent();
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
        if (!$this->validate()) {
            Yii::error(['category.validate', $this->errors], 'podium');
            return false;
        }
        if (!$this->save(false)) {
            Yii::error(['category.create', $this->errors], 'podium');
            return false;
        }
        $this->afterCreate();
        return true;
    }

    public function afterCreate(): void
    {
        $this->trigger(self::EVENT_AFTER_CREATING, new CategoryEvent([
            'category' => $this
        ]));
    }

    /**
     * @return bool
     */
    public function beforeEdit(): bool
    {
        $event = new CategoryEvent();
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
        if (!$this->validate()) {
            Yii::error(['category.validate', $this->errors], 'podium');
            return false;
        }
        if (!$this->save(false)) {
            Yii::error(['category.edit', $this->errors], 'podium');
            return false;
        }
        $this->afterEdit();
        return true;
    }

    public function afterEdit(): void
    {
        $this->trigger(self::EVENT_AFTER_EDITING, new CategoryEvent([
            'category' => $this
        ]));
    }
}
