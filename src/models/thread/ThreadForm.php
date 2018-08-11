<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\repos\ThreadRepo;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * Class ThreadForm
 * @package bizley\podium\api\models\thread
 */
class ThreadForm extends ThreadRepo implements CategorisedFormInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.thread.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.thread.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.thread.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.thread.editing.after';

    /**
     * @param MembershipInterface $author
     */
    public function setAuthor(MembershipInterface $author): void
    {
        $this->author_id = $author->getId();
    }

    /**
     * @param ModelInterface $forum
     */
    public function setForum(ModelInterface $forum): void
    {
        $this->forum_id = $forum->getId();
        $this->category_id = $forum->getParent()->getId();
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
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t('podium.label', 'thread.name'),
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
            Yii::error(['Error while creating thread', $this->errors], 'podium');
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
            Yii::error(['Error while editing thread', $this->errors], 'podium');
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
     * @param ModelInterface $category
     * @throws NotSupportedException
     */
    public function setCategory(ModelInterface $category): void
    {
        throw new NotSupportedException('Thread category can not be set directly.');
    }

    /**
     * @param ModelInterface $thread
     * @throws NotSupportedException
     */
    public function setThread(ModelInterface $thread): void
    {
        throw new NotSupportedException('Thread can not be a child of Thread.');
    }
}
