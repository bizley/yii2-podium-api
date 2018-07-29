<?php

declare(strict_types=1);

namespace bizley\podium\api\models;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\repos\CategoryRepo;

/**
 * Class Category
 * @package bizley\podium\api\models
 */
class Category extends CategoryRepo implements ModelInterface
{
    use ModelTrait;
}
