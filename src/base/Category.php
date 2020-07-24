<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\AuthoredFormInterface;
use bizley\podium\api\interfaces\CategoryInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\SorterInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
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
class Category extends Component implements CategoryInterface
{
    /**
     * @var string|array|ModelInterface category handler
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $modelHandler = \bizley\podium\api\models\category\Category::class;

    /**
     * @var string|array|AuthoredFormInterface category form handler
     * Component ID, class, configuration array, or instance of AuthoredFormInterface.
     */
    public $formHandler = \bizley\podium\api\models\category\CategoryForm::class;

    /**
     * @var string|array|SorterInterface category sorter handler
     * Component ID, class, configuration array, or instance of SorterInterface.
     */
    public $sorterHandler = \bizley\podium\api\models\category\CategorySorter::class;

    /**
     * @var string|array|RemoverInterface category remover handler
     * Component ID, class, configuration array, or instance of RemoverInterface.
     */
    public $removerHandler = \bizley\podium\api\models\category\CategoryRemover::class;

    /**
     * @var string|array|ArchiverInterface category archiver handler
     * Component ID, class, configuration array, or instance of ArchivableInterface.
     */
    public $archiverHandler = \bizley\podium\api\models\category\CategoryArchiver::class;

    /**
     * @throws InvalidConfigException
     */
    public function init() // BC signature
    {
        parent::init();

        $this->modelHandler = Instance::ensure($this->modelHandler, ModelInterface::class);
        $this->formHandler = Instance::ensure($this->formHandler, AuthoredFormInterface::class);
        $this->sorterHandler = Instance::ensure($this->sorterHandler, SorterInterface::class);
        $this->removerHandler = Instance::ensure($this->removerHandler, RemoverInterface::class);
        $this->archiverHandler = Instance::ensure($this->archiverHandler, ArchiverInterface::class);
    }

    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getById(int $id): ?ModelInterface
    {
        $categoryClass = $this->modelHandler;

        return $categoryClass::findById($id);
    }

    /**
     * @param DataFilter|null $filter
     * @param bool|array|Sort|null $sort
     * @param bool|array|Pagination|null $pagination
     * @return DataProviderInterface
     */
    public function getAll(DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $categoryClass = $this->modelHandler;

        return $categoryClass::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @param int|null $id
     * @return AuthoredFormInterface|null
     */
    public function getForm(int $id = null): ?AuthoredFormInterface
    {
        $handler = $this->formHandler;

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
        $categoryForm = $this->getForm();

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

        $categoryForm = $this->getForm((int)$id);

        if ($categoryForm === null) {
            throw new ModelNotFoundException('Category of given ID can not be found.');
        }

        if (!$categoryForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $categoryForm->edit();
    }

    /**
     * @param int $id
     * @return RemoverInterface|null
     */
    public function getRemover(int $id): ?RemoverInterface
    {
        $handler = $this->removerHandler;

        return $handler::findById($id);
    }

    /**
     * Deletes category.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function remove(int $id): PodiumResponse
    {
        $categoryRemover = $this->getRemover($id);

        if ($categoryRemover === null) {
            throw new ModelNotFoundException('Category of given ID can not be found.');
        }

        return $categoryRemover->remove();
    }

    /**
     * @return SorterInterface
     */
    public function getSorter(): SorterInterface
    {
        return new $this->sorterHandler;
    }

    /**
     * Sorts categories.
     * @param array $data
     * @return PodiumResponse
     */
    public function sort(array $data = []): PodiumResponse
    {
        $categorySorter = $this->getSorter();

        if (!$categorySorter->loadData($data)) {
            return PodiumResponse::error();
        }

        return $categorySorter->sort();
    }

    /**
     * @param int $id
     * @return ArchiverInterface|null
     */
    public function getArchiver(int $id): ?ArchiverInterface
    {
        $handler = $this->archiverHandler;

        return $handler::findById($id);
    }

    /**
     * Archives category.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function archive(int $id): PodiumResponse
    {
        $categoryArchiver = $this->getArchiver($id);

        if ($categoryArchiver === null) {
            throw new ModelNotFoundException('Category of given ID can not be found.');
        }

        return $categoryArchiver->archive();
    }

    /**
     * Revives category.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function revive(int $id): PodiumResponse
    {
        $categoryArchiver = $this->getArchiver($id);

        if ($categoryArchiver === null) {
            throw new ModelNotFoundException('Category of given ID can not be found.');
        }

        return $categoryArchiver->revive();
    }
}
