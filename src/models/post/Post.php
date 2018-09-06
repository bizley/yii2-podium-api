<?php

declare(strict_types=1);

namespace bizley\podium\api\models\post;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\repos\PostRepo;
use yii\base\NotSupportedException;
use yii\db\ActiveQuery;

/**
 * Class Post
 * @package bizley\podium\api\models\post
 *
 * @property ModelInterface $parent
 * @property Thread $thread
 */
class Post extends PostRepo implements ModelInterface
{
    use ModelTrait;

    /**
     * @return ModelInterface|null
     */
    public function getParent(): ?ModelInterface
    {
        return $this->thread;
    }

    /**
     * @return ActiveQuery
     */
    public function getThread(): ActiveQuery
    {
        return $this->hasOne(Thread::class, ['id' => 'thread_id']);
    }

    /**
     * @return int
     * @throws NotSupportedException
     */
    public function getPostsCount(): int
    {
        throw new NotSupportedException('Post has got no posts.');
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return (bool) $this->archived;
    }
}
