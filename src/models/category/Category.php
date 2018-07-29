<?php

declare(strict_types=1);

namespace bizley\podium\api\models\category;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\repos\CategoryRepo;

/**
 * Class Category
 * @package bizley\podium\api\models\category
 */
class Category extends CategoryRepo implements ModelInterface
{
    use ModelTrait;
}
