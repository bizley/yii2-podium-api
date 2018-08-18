<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\models\forum\Forum;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\repos\ThreadRepo;

/**
 * Class Thread
 * @package bizley\podium\api\models\thread
 */
class Thread extends ThreadRepo implements ModelInterface
{
    use ModelTrait;

    /**
     * @return ModelInterface
     */
    public function getParent(): ModelInterface
    {
        return Forum::findById($this->forum_id);
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
