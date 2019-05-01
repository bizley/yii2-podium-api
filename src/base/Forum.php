<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\ForumInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\SortableInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

/**
 * Class Forum
 * @package bizley\podium\api\base
 */
class Forum extends Component implements ForumInterface
{
    /**
     * @var string|array|ModelInterface forum handler
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $modelHandler = \bizley\podium\api\models\forum\Forum::class;

    /**
     * @var string|array|CategorisedFormInterface forum form handler
     * Component ID, class, configuration array, or instance of CategorisedFormInterface.
     */
    public $formHandler = \bizley\podium\api\models\forum\ForumForm::class;

    /**
     * @var string|array|SortableInterface forum sorter handler
     * Component ID, class, configuration array, or instance of SortableInterface.
     */
    public $sorterHandler = \bizley\podium\api\models\forum\ForumSorter::class;

    /**
     * @var string|array|RemoverInterface category remover handler
     * Component ID, class, configuration array, or instance of RemoverInterface.
     */
    public $removerHandler = \bizley\podium\api\models\forum\ForumRemover::class;

    /**
     * @var string|array|ArchiverInterface category archiver handler
     * Component ID, class, configuration array, or instance of ArchivableInterface.
     */
    public $archiverHandler = \bizley\podium\api\models\forum\ForumArchiver::class;

    /**
     * @var string|array|MoverInterface category mover handler
     * Component ID, class, configuration array, or instance of MovableInterface.
     */
    public $moverHandler = \bizley\podium\api\models\forum\ForumMover::class;

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->modelHandler = Instance::ensure($this->modelHandler, ModelInterface::class);
        $this->formHandler = Instance::ensure($this->formHandler, CategorisedFormInterface::class);
        $this->sorterHandler = Instance::ensure($this->sorterHandler, SortableInterface::class);
        $this->removerHandler = Instance::ensure($this->removerHandler, RemoverInterface::class);
        $this->archiverHandler = Instance::ensure($this->archiverHandler, ArchiverInterface::class);
        $this->moverHandler = Instance::ensure($this->moverHandler, MoverInterface::class);
    }

    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getById(int $id): ?ModelInterface
    {
        $forumClass = $this->modelHandler;

        return $forumClass::findById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getAll(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $forumClass = $this->modelHandler;

        return $forumClass::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @param int|null $id
     * @return CategorisedFormInterface|null
     */
    public function getForm(?int $id = null): ?CategorisedFormInterface
    {
        $handler = $this->formHandler;

        if ($id === null) {
            return new $handler;
        }

        return $handler::findById($id);
    }

    /**
     * Creates forum.
     * @param array $data
     * @param MembershipInterface $author
     * @param ModelInterface $category
     * @return PodiumResponse
     */
    public function create(array $data, MembershipInterface $author, ModelInterface $category): PodiumResponse
    {
        /* @var $forumForm CategorisedFormInterface */
        $forumForm = $this->getForm();

        $forumForm->setAuthor($author);
        $forumForm->setCategory($category);

        if (!$forumForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $forumForm->create();
    }

    /**
     * Updates forum.
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

        $forumForm = $this->getForm((int)$id);

        if ($forumForm === null) {
            throw new ModelNotFoundException('Forum of given ID can not be found.');
        }

        if (!$forumForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $forumForm->edit();
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
     * Deletes forum.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function remove(int $id): PodiumResponse
    {
        $forumRemover = $this->getRemover($id);

        if ($forumRemover === null) {
            throw new ModelNotFoundException('Forum of given ID can not be found.');
        }

        return $forumRemover->remove();
    }

    /**
     * @return SortableInterface
     */
    public function getSorter(): SortableInterface
    {
        return new $this->sorterHandler;
    }

    /**
     * Sorts forums.
     * @param ModelInterface $category
     * @param array $data
     * @return PodiumResponse
     */
    public function sort(ModelInterface $category, array $data = []): PodiumResponse
    {
        $forumSorter = $this->getSorter();

        $forumSorter->setCategory($category);

        if (!$forumSorter->loadData($data)) {
            return PodiumResponse::error();
        }

        return $forumSorter->sort();
    }

    /**
     * @param int $id
     * @return MoverInterface|null
     */
    public function getMover(int $id): ?MoverInterface
    {
        $handler = $this->moverHandler;

        return $handler::findById($id);
    }

    /**
     * Moves forum to different category.
     * @param int $id
     * @param ModelInterface $category
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function move(int $id, ModelInterface $category): PodiumResponse
    {
        $forumMover = $this->getMover($id);

        if ($forumMover === null) {
            throw new ModelNotFoundException('Forum of given ID can not be found.');
        }

        $forumMover->prepareCategory($category);

        return $forumMover->move();
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
     * Archives forum.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function archive(int $id): PodiumResponse
    {
        $forumArchiver = $this->getArchiver($id);

        if ($forumArchiver === null) {
            throw new ModelNotFoundException('Forum of given ID can not be found.');
        }

        return $forumArchiver->archive();
    }

    /**
     * Revives forum.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function revive(int $id): PodiumResponse
    {
        $forumArchiver = $this->getArchiver($id);

        if ($forumArchiver === null) {
            throw new ModelNotFoundException('Forum of given ID can not be found.');
        }

        return $forumArchiver->revive();
    }
}
