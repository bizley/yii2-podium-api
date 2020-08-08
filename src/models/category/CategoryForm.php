<?php

declare(strict_types=1);

namespace bizley\podium\api\models\category;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\AuthoredFormInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * Class CategoryForm
 * @package bizley\podium\api\models\category
 */
class CategoryForm extends Category implements AuthoredFormInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.category.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.category.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.category.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.category.editing.after';

    /**
     * @param MembershipInterface $author
     * @throws InsufficientDataException
     */
    public function setAuthor(MembershipInterface $author): void
    {
        $authorId = $author->getId();
        if ($authorId === null) {
            throw new InsufficientDataException('Missing author Id for category form');
        }
        $this->author_id = $authorId;
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
            [['name', 'visible', 'sort'], 'required'],
            [['name', 'description', 'slug'], 'string', 'max' => 255],
            [['visible'], 'boolean'],
            [['sort'], 'integer'],
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
            'name' => Yii::t('podium.label', 'category.name'),
            'description' => Yii::t('podium.label', 'category.description'),
            'visible' => Yii::t('podium.label', 'category.visible'),
            'sort' => Yii::t('podium.label', 'category.sort'),
            'slug' => Yii::t('podium.label', 'category.slug'),
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
    public function beforeCreate(): bool
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_CREATING, $event);

        return $event->canCreate;
    }

    /**
     * @return PodiumResponse
     */
    public function create(): PodiumResponse
    {
        if (!$this->beforeCreate()) {
            return PodiumResponse::error();
        }

        if (!$this->save()) {
            Yii::error(['Error while creating category', $this->errors], 'podium');

            return PodiumResponse::error($this);
        }

        $this->afterCreate();

        return PodiumResponse::success($this->getOldAttributes());
    }

    public function afterCreate(): void
    {
        $this->trigger(self::EVENT_AFTER_CREATING, new ModelEvent(['model' => $this]));
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
            Yii::error(['Error while editing category', $this->errors], 'podium');

            return PodiumResponse::error($this);
        }

        $this->afterEdit();

        return PodiumResponse::success($this->getOldAttributes());
    }

    public function afterEdit(): void
    {
        $this->trigger(self::EVENT_AFTER_EDITING, new ModelEvent(['model' => $this]));
    }
}
