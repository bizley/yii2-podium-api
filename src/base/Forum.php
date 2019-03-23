<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ArchivableInterface;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\ForumInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MovableInterface;
use bizley\podium\api\interfaces\RemovableInterface;
use bizley\podium\api\interfaces\SortableInterface;
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
class Forum extends PodiumComponent implements ForumInterface
{
    /**
     * @var string|array|ModelInterface forum handler
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $forumHandler = \bizley\podium\api\models\forum\Forum::class;

    /**
     * @var string|array|CategorisedFormInterface forum form handler
     * Component ID, class, configuration array, or instance of CategorisedFormInterface.
     */
    public $forumFormHandler = \bizley\podium\api\models\forum\ForumForm::class;

    /**
     * @var string|array|SortableInterface forum sorter handler
     * Component ID, class, configuration array, or instance of SortableInterface.
     */
    public $forumSorterHandler = \bizley\podium\api\models\forum\ForumSorter::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->forumHandler = Instance::ensure($this->forumHandler, ModelInterface::class);
        $this->forumFormHandler = Instance::ensure($this->forumFormHandler, CategorisedFormInterface::class);
        $this->forumSorterHandler = Instance::ensure($this->forumSorterHandler, SortableInterface::class);
    }

    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getForumById(int $id): ?ModelInterface
    {
        $forumClass = $this->forumHandler;

        return $forumClass::findById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getForums(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $forumClass = $this->forumHandler;

        return $forumClass::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @param int|null $id
     * @return CategorisedFormInterface|null
     */
    public function getForumForm(?int $id = null): ?CategorisedFormInterface
    {
        $handler = $this->forumFormHandler;

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
        $forumForm = $this->getForumForm();

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

        $forumForm = $this->getForumForm((int)$id);

        if ($forumForm === null) {
            throw new ModelNotFoundException('Forum of given ID can not be found.');
        }

        if (!$forumForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $forumForm->edit();
    }

    /**
     * Deletes forum.
     * @param RemovableInterface $forumRemover
     * @return PodiumResponse
     */
    public function remove(RemovableInterface $forumRemover): PodiumResponse
    {
        return $forumRemover->remove();
    }

    /**
     * @return SortableInterface
     */
    public function getForumSorter(): SortableInterface
    {
        return new $this->forumSorterHandler;
    }

    /**
     * Sorts forums.
     * @param ModelInterface $category
     * @param array $data
     * @return PodiumResponse
     */
    public function sort(ModelInterface $category, array $data = []): PodiumResponse
    {
        $forumSorter = $this->getForumSorter();

        $forumSorter->setCategory($category);

        if (!$forumSorter->loadData($data)) {
            return PodiumResponse::error();
        }

        return $forumSorter->sort();
    }

    /**
     * Moves forum to different category.
     * @param MovableInterface $forumMover
     * @param ModelInterface $category
     * @return PodiumResponse
     */
    public function move(MovableInterface $forumMover, ModelInterface $category): PodiumResponse
    {
        $forumMover->setCategory($category);

        return $forumMover->move();
    }

    /**
     * Archives forum.
     * @param ArchivableInterface $forumArchiver
     * @return PodiumResponse
     */
    public function archive(ArchivableInterface $forumArchiver): PodiumResponse
    {
        return $forumArchiver->archive();
    }

    /**
     * Revives forum.
     * @param ArchivableInterface $forumArchiver
     * @return PodiumResponse
     */
    public function revive(ArchivableInterface $forumArchiver): PodiumResponse
    {
        return $forumArchiver->revive();
    }
}
