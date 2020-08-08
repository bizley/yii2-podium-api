<?php

declare(strict_types=1);

namespace bizley\podium\api\models\forum;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * Class ForumForm
 * @package bizley\podium\api\models\forum
 */
class ForumForm extends Forum implements CategorisedFormInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.forum.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.forum.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.forum.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.forum.editing.after';

    /**
     * @param MembershipInterface $author
     * @throws InsufficientDataException
     */
    public function setAuthor(MembershipInterface $author): void
    {
        $authorId = $author->getId();
        if ($authorId === null) {
            throw new InsufficientDataException('Missing author Id for forum form');
        }
        $this->author_id = $authorId;
    }

    /**
     * @param ModelInterface $category
     * @throws InsufficientDataException
     */
    public function setCategory(ModelInterface $category): void
    {
        $categoryId = $category->getId();
        if ($categoryId === null) {
            throw new InsufficientDataException('Missing category Id for forum form');
        }
        $this->category_id = $categoryId;
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
            [['name', 'slug'], 'string', 'max' => 255],
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
            'name' => Yii::t('podium.label', 'forum.name'),
            'visible' => Yii::t('podium.label', 'forum.visible'),
            'sort' => Yii::t('podium.label', 'forum.sort'),
            'slug' => Yii::t('podium.label', 'forum.slug'),
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
            Yii::error(['Error while creating forum', $this->errors], 'podium');

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
            Yii::error(['Error while editing forum', $this->errors], 'podium');

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
     * @param ModelInterface $forum
     * @throws NotSupportedException
     */
    public function setForum(ModelInterface $forum): void
    {
        throw new NotSupportedException('Forum can not be a child of Forum.');
    }

    /**
     * @param ModelInterface $thread
     * @throws NotSupportedException
     */
    public function setThread(ModelInterface $thread): void
    {
        throw new NotSupportedException('Forum can not be a child of Thread.');
    }
}
