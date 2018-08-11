<?php

declare(strict_types=1);

namespace bizley\podium\api\models\post;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\repos\PostRepo;

/**
 * Class Thread
 * @package bizley\podium\api\models\post
 */
class Post extends PostRepo implements ModelInterface
{
    use ModelTrait;

    /**
     * @return ModelInterface
     */
    public function getParent(): ModelInterface
    {
        return Thread::findById($this->thread_id);
    }
}
