<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\CategoryFormInterface;
use bizley\podium\api\interfaces\CategoryInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;
use yii\di\Instance;

/**
 * Class Category
 * @package bizley\podium\api\base
 *
 * @property CategoryFormInterface $categoryForm
 * @property ModelInterface $categoryModel
 */
class Category extends PodiumComponent implements CategoryInterface
{
    /**
     * @var string|array|ModelInterface
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $categoryHandler = \bizley\podium\api\models\category\Category::class;

    /**
     * @var string|array|CategoryFormInterface
     * Component ID, class, configuration array, or instance of CategoryFormInterface.
     */
    public $categoryFormHandler = \bizley\podium\api\models\category\CategoryForm::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->categoryHandler = Instance::ensure($this->categoryHandler, ModelInterface::class);
        $this->categoryFormHandler = Instance::ensure($this->categoryFormHandler, CategoryFormInterface::class);
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
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getCategories(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $categoryModel = $this->getCategoryModel();
        return $categoryModel::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @return CategoryFormInterface
     */
    public function getCategoryForm(): CategoryFormInterface
    {
        return $this->categoryFormHandler;
    }

    /**
     * @param array $data
     * @param MembershipInterface $author
     * @return bool
     */
    public function create(array $data, MembershipInterface $author): bool
    {
        $categoryForm = $this->getCategoryForm();
        $categoryForm->setAuthor($author);

        if (!$categoryForm->loadData($data)) {
            return false;
        }
        return $categoryForm->create();
    }

    /**
     * @param CategoryFormInterface $categoryForm
     * @param array $data
     * @return bool
     */
    public function edit(CategoryFormInterface $categoryForm, array $data): bool
    {
        if (!$categoryForm->loadData($data)) {
            return false;
        }
        return $categoryForm->edit();
    }
}
