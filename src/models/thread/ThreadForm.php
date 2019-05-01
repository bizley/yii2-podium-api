<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use Throwable;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;

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
     */
    public function setAuthor(MembershipInterface $author): void
    {
        $this->author_id = $author->getId();
    }

    /**
     * @param ModelInterface $forum
     * @throws Exception
     */
    public function setForum(ModelInterface $forum): void
    {
        $this->setForumModel($forum);

        $this->forum_id = $forum->getId();

        $category = $forum->getParent();
        if ($category === null) {
            throw new Exception('Can not find parent category!');
        }

        $this->category_id = $category->getId();
    }

    private $_forum;

    /**
     * @param ModelInterface $forum
     */
    public function setForumModel(ModelInterface $forum): void
    {
        $this->_forum = $forum;
    }

    /**
     * @return ModelInterface
     */
    public function getForumModel(): ModelInterface
    {
        return $this->_forum;
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
            [['name'], 'required'],
            [['name', 'slug'], 'string', 'max' => 255],
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
            'name' => Yii::t('podium.label', 'thread.name'),
            'slug' => Yii::t('podium.label', 'thread.slug'),
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

        if (!$this->validate()) {
            return PodiumResponse::error($this);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->save(false)) {
                throw new Exception('Error while creating thread');
            }

            if (!$this->getForumModel()->updateCounters(['threads_count' => 1])) {
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
            Yii::error(['Error while editing thread', $this->errors], 'podium');

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
