<?php

declare(strict_types=1);

namespace bizley\podium\api\models\post;

use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\repos\PostRepo;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;

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
        $this->thread_id = $thread->getId();
        $forum = $thread->getParent();
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
        if (!$this->save()) {
            Yii::error(['Error while creating post', $this->errors], 'podium');
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
