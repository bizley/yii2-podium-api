<?php

declare(strict_types=1);

namespace bizley\podium\api\models\forum;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\models\category\Category;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\repos\ForumRepo;

/**
 * Class Forum
 * @package bizley\podium\api\models\forum
 */
class Forum extends ForumRepo implements ModelInterface
{
    use ModelTrait;

    /**
     * @return ModelInterface
     */
    public function getParent(): ModelInterface
    {
        return Category::findById($this->category_id);
    }
}
