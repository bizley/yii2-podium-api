<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ModelInterface;
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
     * @var string|array|ModelInterface
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $categoryHandler = \bizley\podium\api\models\Category::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->categoryHandler = Instance::ensure($this->categoryHandler, ModelInterface::class);
    }

    /**
     * @return ModelInterface
     */
    public function getCategoryModel(): ModelInterface
    {
        return $this->categoryHandler;
    }

    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getCategoryById(int $id): ?ModelInterface
    {
        $categoryModel = $this->getCategoryModel();
        return $categoryModel::findById($id);
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
        return $categoryModel::findByFilter($filter, $sort, $pagination);
    }
}
