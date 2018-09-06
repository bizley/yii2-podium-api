<?php

declare(strict_types=1);

namespace bizley\podium\api\models\forum;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\models\category\Category;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\repos\ForumRepo;
use yii\db\ActiveQuery;

/**
 * Class Forum
 * @package bizley\podium\api\models\forum
 *
 * @property ModelInterface $parent
 * @property Category $category
 * @property int $postsCount
 */
class Forum extends ForumRepo implements ModelInterface
{
    use ModelTrait;

    /**
     * @return ModelInterface|null
     */
    public function getParent(): ?ModelInterface
    {
        return $this->category;
    }

    /**
     * @return ActiveQuery
     */
    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
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
