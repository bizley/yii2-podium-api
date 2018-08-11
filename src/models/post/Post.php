<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\repos\PostRepo;

/**
 * Class Thread
 * @package bizley\podium\api\models\thread
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
