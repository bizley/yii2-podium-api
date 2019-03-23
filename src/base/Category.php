<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ArchivableInterface;
use bizley\podium\api\interfaces\AuthoredFormInterface;
use bizley\podium\api\interfaces\CategoryInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RemovableInterface;
use bizley\podium\api\interfaces\SortableInterface;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

/**
 * Class Category
 * @package bizley\podium\api\base
 */
class Category extends PodiumComponent implements CategoryInterface
{
    /**
     * @var string|array|ModelInterface category handler
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $categoryHandler = \bizley\podium\api\models\category\Category::class;

    /**
     * @var string|array|AuthoredFormInterface category form handler
     * Component ID, class, configuration array, or instance of AuthoredFormInterface.
     */
    public $categoryFormHandler = \bizley\podium\api\models\category\CategoryForm::class;

    /**
     * @var string|array|SortableInterface category sorter handler
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
     * @param int $id
     * @return ModelInterface|null
     */
    public function getCategoryById(int $id): ?ModelInterface
    {
        $categoryClass = $this->categoryHandler;

        return $categoryClass::findById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getCategories(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $categoryClass = $this->categoryHandler;

        return $categoryClass::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @param int|null $id
     * @return AuthoredFormInterface|null
     */
    public function getCategoryForm(?int $id = null): ?AuthoredFormInterface
    {
        $handler = $this->categoryFormHandler;

        if ($id === null) {
            return new $handler;
        }

        return $handler::findById($id);
    }

    /**
     * Creates category.
     * @param array $data
     * @param MembershipInterface $author
     * @return PodiumResponse
     */
    public function create(array $data, MembershipInterface $author): PodiumResponse
    {
        /* @var $categoryForm AuthoredFormInterface */
        $categoryForm = $this->getCategoryForm();

        $categoryForm->setAuthor($author);

        if (!$categoryForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $categoryForm->create();
    }

    /**
     * Updates category.
     * @param array $data
     * @return PodiumResponse
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function edit(array $data): PodiumResponse
    {
        $id = ArrayHelper::remove($data, 'id');

        if ($id === null) {
            throw new InsufficientDataException('ID key is missing.');
        }

        $categoryForm = $this->getCategoryForm((int)$id);

        if ($categoryForm === null) {
            throw new ModelNotFoundException('Category of given ID can not be found.');
        }

        if (!$categoryForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $categoryForm->edit();
    }

    /**
     * Deletes category.
     * @param RemovableInterface $categoryRemover
     * @return PodiumResponse
     */
    public function remove(RemovableInterface $categoryRemover): PodiumResponse
    {
        return $categoryRemover->remove();
    }

    /**
     * @return SortableInterface
     */
    public function getCategorySorter(): SortableInterface
    {
        return new $this->categorySorterHandler;
    }

    /**
     * Sorts categories.
     * @param array $data
     * @return PodiumResponse
     */
    public function sort(array $data = []): PodiumResponse
    {
        $categorySorter = $this->getCategorySorter();

        if (!$categorySorter->loadData($data)) {
            return PodiumResponse::error();
        }

        return $categorySorter->sort();
    }

    /**
     * Archives category.
     * @param ArchivableInterface $categoryArchiver
     * @return PodiumResponse
     */
    public function archive(ArchivableInterface $categoryArchiver): PodiumResponse
    {
        return $categoryArchiver->archive();
    }

    /**
     * Revives category.
     * @param ArchivableInterface $categoryArchiver
     * @return PodiumResponse
     */
    public function revive(ArchivableInterface $categoryArchiver): PodiumResponse
    {
        return $categoryArchiver->revive();
    }
}
