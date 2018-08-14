<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ArchivableInterface;
use bizley\podium\api\interfaces\AuthoredFormInterface;
use bizley\podium\api\interfaces\CategoryInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RemovableInterface;
use bizley\podium\api\interfaces\SortableInterface;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;
use yii\di\Instance;

/**
 * Class Category
 * @package bizley\podium\api\base
 *
 * @property AuthoredFormInterface $categoryForm
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
     * @var string|array|AuthoredFormInterface
     * Component ID, class, configuration array, or instance of AuthoredFormInterface.
     */
    public $categoryFormHandler = \bizley\podium\api\models\category\CategoryForm::class;

    /**
     * @var string|array|SortableInterface
     * Component ID, class, configuration array, or instance of SortableInterface.
     */
    public $categorySorterHandler = \bizley\podium\api\models\category\CategorySorter::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->categoryHandler = Instance::ensure($this->categoryHandler, ModelInterface::class);
        $this->categoryFormHandler = Instance::ensure($this->categoryFormHandler, AuthoredFormInterface::class);
        $this->categorySorterHandler = Instance::ensure($this->categorySorterHandler, SortableInterface::class);
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
     * @return AuthoredFormInterface
     */
    public function getCategoryForm(): AuthoredFormInterface
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
     * @param ModelFormInterface $categoryForm
     * @param array $data
     * @return bool
     */
    public function edit(ModelFormInterface $categoryForm, array $data): bool
    {
        if (!$categoryForm->loadData($data)) {
            return false;
        }
        return $categoryForm->edit();
    }

    /**
     * @param RemovableInterface $categoryRemover
     * @return bool
     */
    public function remove(RemovableInterface $categoryRemover): bool
    {
        return $categoryRemover->remove();
    }

    /**
     * @return SortableInterface
     */
    public function getCategorySorter(): SortableInterface
    {
        return $this->categorySorterHandler;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function sort(array $data = []): bool
    {
        $categorySorter = $this->getCategorySorter();

        if (!$categorySorter->loadData($data)) {
            return false;
        }
        return $categorySorter->sort();
    }

    /**
     * @param ArchivableInterface $categoryArchiver
     * @return bool
     */
    public function archive(ArchivableInterface $categoryArchiver): bool
    {
        return $categoryArchiver->archive();
    }

    /**
     * @param ArchivableInterface $categoryArchiver
     * @return bool
     */
    public function revive(ArchivableInterface $categoryArchiver): bool
    {
        return $categoryArchiver->revive();
    }
}
