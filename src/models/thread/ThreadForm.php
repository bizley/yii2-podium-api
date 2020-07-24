<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use Throwable;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\db\Transaction;

/**
 * Class ThreadForm
 * @package bizley\podium\api\models\thread
 */
class ThreadForm extends Thread implements CategorisedFormInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.thread.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.thread.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.thread.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.thread.editing.after';

    /**
     * @param MembershipInterface $author
     * @throws InsufficientDataException
     */
    public function setAuthor(MembershipInterface $author): void
    {
        $authorId = $author->getId();
        if ($authorId === null) {
            throw new InsufficientDataException('Missing author Id for thread form');
        }
        $this->author_id = $authorId;
    }

    /**
     * @param ModelInterface $forum
     * @throws Exception
     * @throws InsufficientDataException
     */
    public function setForum(ModelInterface $forum): void
    {
        $this->setForumModel($forum);

        $forumId = $forum->getId();
        if ($forumId === null) {
            throw new InsufficientDataException('Missing forum Id for thread form');
        }
        $this->forum_id = $forumId;

        $category = $forum->getParent();
        if ($category === null) {
            throw new Exception('Can not find parent category!');
        }

        $categoryId = $category->getId();
        if ($categoryId === null) {
            throw new InsufficientDataException('Missing forum parent Id for thread form');
        }
        $this->category_id = $categoryId;
    }

    private ?ModelInterface $forum = null;

    /**
     * @param ModelInterface $forum
     */
    public function setForumModel(ModelInterface $forum): void
    {
        $this->forum = $forum;
    }

    /**
     * @return ModelInterface|null
     */
    public function getForumModel(): ?ModelInterface
    {
        return $this->forum;
    }

    /**
     * Adds TimestampBehavior and SluggableBehavior.
     * @return array<int|string, mixed>
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
     * @return array<int|string, mixed>
     */
    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name', 'slug'], 'string', 'max' => 255],
            [['slug'], 'match', 'pattern' => '/^[a-zA-Z0-9\-]{0,255}$/'],
            [['slug'], 'unique'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t('podium.label', 'thread.name'),
            'slug' => Yii::t('podium.label', 'thread.slug'),
        ];
    }

    /**
     * @param array<string|int, mixed> $data
     * @return bool
     */
    public function loadData(array $data = []): bool
    {
        return $this->load($data, '');
    }

    /**
     * Executes before create().
     * @return bool
     */
    public function beforeCreate(): bool
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_CREATING, $event);

        return $event->canCreate;
    }

    /**
     * Creates new thread.
     * @return PodiumResponse
     */
    public function create(): PodiumResponse
    {
        if (!$this->beforeCreate()) {
            return PodiumResponse::error();
        }

        if (!$this->validate()) {
            return PodiumResponse::error($this);
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->save(false)) {
                throw new Exception('Error while creating thread');
            }

            if (
                ($forum = $this->getForumModel())
                && !$forum->updateCounters(['threads_count' => 1])
            ) {
                throw new Exception('Error while updating forum counters!');
            }

            $this->afterCreate();
            $transaction->commit();

            return PodiumResponse::success($this->getOldAttributes());
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while creating thread', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    /**
     * Executes after successful create().
     */
    public function afterCreate(): void
    {
        $this->trigger(self::EVENT_AFTER_CREATING, new ModelEvent(['model' => $this]));
    }

    /**
     * Executes before edit().
     * @return bool
     */
    public function beforeEdit(): bool
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_EDITING, $event);

        return $event->canEdit;
    }

    /**
     * Edits the thread.
     * @return PodiumResponse
     */
    public function edit(): PodiumResponse
    {
        if (!$this->beforeEdit()) {
            return PodiumResponse::error();
        }

        if (!$this->save()) {
            Yii::error(['Error while editing thread', $this->errors], 'podium');

            return PodiumResponse::error($this);
        }

        $this->afterEdit();

        return PodiumResponse::success($this->getOldAttributes());
    }

    /**
     * Executes after successful edit().
     */
    public function afterEdit(): void
    {
        $this->trigger(self::EVENT_AFTER_EDITING, new ModelEvent(['model' => $this]));
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
