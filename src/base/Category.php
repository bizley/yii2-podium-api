<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\CategoryModelInterface;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\di\Instance;

/**
 * Class Category
 * @package bizley\podium\api\base
 */
class Category extends PodiumComponent
{
    /**
     * @var string|array|CategoryModelInterface
     * Component ID, class, configuration array, or instance of CategoryModelInterface.
     */
    public $categoryHandler = \bizley\podium\api\models\Category::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->categoryHandler = Instance::ensure($this->categoryHandler, CategoryModelInterface::class);
    }

    /**
     * @return CategoryModelInterface
     */
    public function getCategoryModel(): CategoryModelInterface
    {
        return $this->categoryHandler;
    }

    /**
     * @param int $id
     * @return CategoryModelInterface|null
     */
    public function getCategoryById(int $id): ?CategoryModelInterface
    {
        $categoryModel = $this->getCategoryModel();
        return $categoryModel::findCategoryById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null $sort
     * @param null $pagination
     * @return DataProviderInterface
     */
    public function getCategories(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $categoryModel = $this->getCategoryModel();
        return $categoryModel::findCategories($filter, $sort, $pagination);
    }
}
