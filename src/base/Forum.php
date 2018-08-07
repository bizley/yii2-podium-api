<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\ForumInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MovableInterface;
use bizley\podium\api\interfaces\SortableInterface;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;
use yii\di\Instance;

/**
 * Class Forum
 * @package bizley\podium\api\base
 *
 * @property CategorisedFormInterface $forumForm
 * @property SortableInterface $forumSorter
 * @property ModelInterface $forumModel
 */
class Forum extends PodiumComponent implements ForumInterface
{
    /**
     * @var string|array|ModelInterface
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $forumHandler = \bizley\podium\api\models\forum\Forum::class;

    /**
     * @var string|array|CategorisedFormInterface
     * Component ID, class, configuration array, or instance of CategorisedFormInterface.
     */
    public $forumFormHandler = \bizley\podium\api\models\forum\ForumForm::class;

    /**
     * @var string|array|SortableInterface
     * Component ID, class, configuration array, or instance of SortableInterface.
     */
    public $forumSorterHandler = \bizley\podium\api\models\forum\ForumSorter::class;

    /**
     * @var string|array|MovableInterface
     * Component ID, class, configuration array, or instance of MovableInterface.
     */
    public $forumMoverHandler = \bizley\podium\api\models\forum\ForumMover::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->forumHandler = Instance::ensure($this->forumHandler, ModelInterface::class);
        $this->forumFormHandler = Instance::ensure($this->forumFormHandler, CategorisedFormInterface::class);
        $this->forumSorterHandler = Instance::ensure($this->forumSorterHandler, SortableInterface::class);
        $this->forumMoverHandler = Instance::ensure($this->forumMoverHandler, MovableInterface::class);
    }

    /**
     * @return ModelInterface
     */
    public function getForumModel(): ModelInterface
    {
        return $this->forumHandler;
    }

    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getForumById(int $id): ?ModelInterface
    {
        $forumModel = $this->getForumModel();
        return $forumModel::findById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getForums(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $forumModel = $this->getForumModel();
        return $forumModel::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @return CategorisedFormInterface
     */
    public function getForumForm(): CategorisedFormInterface
    {
        return $this->forumFormHandler;
    }

    /**
     * @param array $data
     * @param MembershipInterface $author
     * @param ModelInterface $category
     * @return bool
     */
    public function create(array $data, MembershipInterface $author, ModelInterface $category): bool
    {
        $forumForm = $this->getForumForm();
        $forumForm->setAuthor($author);
        $forumForm->setCategory($category);

        if (!$forumForm->loadData($data)) {
            return false;
        }
        return $forumForm->create();
    }

    /**
     * @param ModelFormInterface $forumForm
     * @param array $data
     * @return bool
     */
    public function edit(ModelFormInterface $forumForm, array $data): bool
    {
        if (!$forumForm->loadData($data)) {
            return false;
        }
        return $forumForm->edit();
    }

    /**
     * @param ModelInterface $forum
     * @return int|false
     */
    public function delete(ModelInterface $forum)
    {
        return $forum->delete();
    }

    /**
     * @return SortableInterface
     */
    public function getForumSorter(): SortableInterface
    {
        return $this->forumSorterHandler;
    }

    /**
     * @param ModelInterface $category
     * @param array $data
     * @return bool
     */
    public function sort(ModelInterface $category, array $data = []): bool
    {
        $forumSorter = $this->getForumSorter();
        $forumSorter->setCategory($category);

        if (!$forumSorter->loadData($data)) {
            return false;
        }
        return $forumSorter->sort();
    }

    /**
     * @return SortableInterface
     */
    public function getForumMover(): MovableInterface
    {
        return $this->forumMoverHandler;
    }

    /**
     * @param MovableInterface $forumMove
     * @param ModelInterface $category
     * @return bool
     */
    public function move(MovableInterface $forumMove, ModelInterface $category): bool
    {
        return $forumMove->move($category);
    }
}
