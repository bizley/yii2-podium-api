<?php

declare(strict_types=1);

namespace bizley\podium\api\models\category;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\repos\CategoryRepo;
use yii\base\NotSupportedException;

/**
 * Class Category
 * @package bizley\podium\api\models\category
 */
class Category extends CategoryRepo implements ModelInterface
{
    use ModelTrait;

    /**
     * @return ModelInterface|null
     * @throws NotSupportedException
     */
    public function getParent(): ?ModelInterface
    {
        throw new NotSupportedException('Category has got no parent.');
    }

    /**
     * @return int
     * @throws NotSupportedException
     */
    public function getPostsCount(): int
    {
        throw new NotSupportedException('Category has got no posts.');
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return (bool) $this->archived;
    }
}
