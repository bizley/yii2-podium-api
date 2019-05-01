<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\models\forum\Forum;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\repos\ThreadRepo;
use yii\db\ActiveQuery;

/**
 * Class Thread
 * @package bizley\podium\api\models\thread
 *
 * @property ModelInterface $parent
 * @property Forum $forum
 * @property int $postsCount
 */
class Thread extends ThreadRepo implements ModelInterface
{
    use ModelTrait;

    /**
     * @return ModelInterface|null
     */
    public function getParent(): ?ModelInterface
    {
        return $this->forum;
    }

    /**
     * @return ActiveQuery
     */
    public function getForum(): ActiveQuery
    {
        return $this->hasOne(Forum::class, ['id' => 'forum_id']);
    }

    /**
     * @return int
     */
    public function getPostsCount(): int
    {
        return $this->posts_count;
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return (bool) $this->archived;
    }
}
