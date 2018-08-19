<?php

declare(strict_types=1);

namespace bizley\podium\api\models\post;

use bizley\podium\api\enums\PostType;
use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\repos\PostRepo;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;

/**
 * Class PostForm
 * @package bizley\podium\api\models\post
 */
class PostForm extends PostRepo implements CategorisedFormInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.post.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.post.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.post.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.post.editing.after';

    /**
     * @param MembershipInterface $author
     */
    public function setAuthor(MembershipInterface $author): void
    {
        $this->author_id = $author->getId();
    }

    /**
     * @param ModelInterface $thread
     */
    public function setThread(ModelInterface $thread): void
    {
        $this->setThreadModel($thread);
        $this->setForumModel($thread->getParent());

        $this->thread_id = $thread->getId();
        $this->forum_id = $this->getForumModel()->getId();
        $this->category_id = $this->getForumModel()->getParent()->getId();
    }

    private $_thread;

    /**
     * @param ModelInterface $thread
     */
    public function setThreadModel(ModelInterface $thread): void
    {
        $this->_thread = $thread;
    }

    /**
     * @return ModelInterface
     */
    public function getThreadModel(): ModelInterface
    {
        return $this->_thread;
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
        ];
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
        return [
            'content' => Yii::t('podium.label', 'post.content'),
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
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->getThreadModel()->updateCounters(['posts_count' => 1])) {
                throw new Exception('Error while updating thread counters!');
            }
            if (!$this->getForumModel()->updateCounters(['posts_count' => 1])) {
                throw new Exception('Error while updating forum counters!');
            }

            $this->type_id = PostType::POST;

            if (!$this->save()) {
                Yii::error(['Error while creating post', $this->errors], 'podium');
                return false;
            }
            $this->afterCreate();

            $transaction->commit();
            return true;

        } catch (\Throwable $exc) {
            Yii::error(['Exception while creating post', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            try {
                $transaction->rollBack();
            } catch (\Throwable $excTrans) {
                Yii::error(['Exception while post creating transaction rollback', $excTrans->getMessage(), $excTrans->getTraceAsString()], 'podium');
            }
        }
        return false;
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
        $this->edited = true;
        $this->edited_at = time();
        if (!$this->save()) {
            Yii::error(['Error while editing post', $this->errors], 'podium');
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
