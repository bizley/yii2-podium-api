<?php

declare(strict_types=1);

namespace bizley\podium\api\models\post;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use Throwable;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\db\Transaction;

use function time;

/**
 * Class PostForm
 * @package bizley\podium\api\models\post
 */
class PostForm extends Post implements CategorisedFormInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.post.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.post.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.post.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.post.editing.after';

    /**
     * @param MembershipInterface $author
     * @throws InsufficientDataException
     */
    public function setAuthor(MembershipInterface $author): void
    {
        $authorId = $author->getId();
        if ($authorId === null) {
            throw new InsufficientDataException('Missing author Id for post form');
        }
        $this->author_id = $authorId;
    }

    /**
     * @param ModelInterface $thread
     * @throws Exception
     * @throws InsufficientDataException
     */
    public function setThread(ModelInterface $thread): void
    {
        $this->setThreadModel($thread);

        $forum = $thread->getParent();
        if ($forum === null) {
            throw new Exception('Can not find parent forum!');
        }
        $this->setForumModel($forum);

        $threadId = $thread->getId();
        if ($threadId === null) {
            throw new InsufficientDataException('Missing thread Id for post form');
        }
        $this->thread_id = $threadId;
        $forumId = $forum->getId();
        if ($forumId === null) {
            throw new InsufficientDataException('Missing parent thread Id for post form');
        }
        $this->forum_id = $forumId;

        $category = $forum->getParent();
        if ($category === null) {
            throw new Exception('Can not find parent category!');
        }
        $categoryId = $category->getId();
        if ($categoryId === null) {
            throw new InsufficientDataException('Missing grandparent thread Id for post form');
        }
        $this->category_id = $categoryId;
    }

    private ?ModelInterface $thread = null;

    /**
     * @param ModelInterface $thread
     */
    public function setThreadModel(ModelInterface $thread): void
    {
        $this->thread = $thread;
    }

    /**
     * @return ModelInterface|null
     */
    public function getThreadModel(): ?ModelInterface
    {
        return $this->thread;
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
     * @return array
     */
    public function behaviors(): array
    {
        return ['timestamp' => TimestampBehavior::class];
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['content'], 'required'],
            [['content'], 'string', 'min' => 3],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return ['content' => Yii::t('podium.label', 'post.content')];
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

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->save(false)) {
                throw new Exception('Error while creating post!');
            }

            if (
                ($thread = $this->getThreadModel())
                && !$thread->updateCounters(['posts_count' => 1])
            ) {
                throw new Exception('Error while updating thread counters!');
            }
            if (
                ($forum = $this->getForumModel())
                && !$forum->updateCounters(['posts_count' => 1])
            ) {
                throw new Exception('Error while updating forum counters!');
            }

            $this->afterCreate();
            $transaction->commit();

            return PodiumResponse::success($this->getOldAttributes());
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while creating post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

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

        $this->edited = true;
        $this->edited_at = time();

        if (!$this->save()) {
            Yii::error(['Error while editing post', $this->errors], 'podium');

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
        throw new NotSupportedException('Post category can not be set directly.');
    }

    /**
     * @param ModelInterface $forum
     * @throws NotSupportedException
     */
    public function setForum(ModelInterface $forum): void
    {
        throw new NotSupportedException('Post forum can not be set directly.');
    }
}
