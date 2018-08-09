<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MovableInterface;
use bizley\podium\api\interfaces\ThreadInterface;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\di\Instance;

/**
 * Class Thread
 * @package bizley\podium\api\base
 *
 * @property CategorisedFormInterface $threadForm
 * @property ModelInterface $threadModel
 * @property MovableInterface $threadMover
 */
class Thread extends PodiumComponent implements ThreadInterface
{
    /**
     * @var string|array|ModelInterface
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $threadHandler = \bizley\podium\api\models\thread\Thread::class;

    /**
     * @var string|array|CategorisedFormInterface
     * Component ID, class, configuration array, or instance of CategorisedFormInterface.
     */
    public $threadFormHandler = \bizley\podium\api\models\thread\ThreadForm::class;

    /**
     * @var string|array|MovableInterface
     * Component ID, class, configuration array, or instance of MovableInterface.
     */
    public $threadMoverHandler = \bizley\podium\api\models\thread\ThreadMover::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->threadHandler = Instance::ensure($this->threadHandler, ModelInterface::class);
        $this->threadFormHandler = Instance::ensure($this->threadFormHandler, CategorisedFormInterface::class);
        $this->threadMoverHandler = Instance::ensure($this->threadMoverHandler, MovableInterface::class);
    }

    /**
     * @return ModelInterface
     */
    public function getThreadModel(): ModelInterface
    {
        return $this->threadHandler;
    }

    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getThreadById(int $id): ?ModelInterface
    {
        $threadModel = $this->getThreadModel();
        return $threadModel::findById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null $sort
     * @param null $pagination
     * @return DataProviderInterface
     */
    public function getThreads(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $threadModel = $this->getThreadModel();
        return $threadModel::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @return CategorisedFormInterface
     */
    public function getThreadForm(): CategorisedFormInterface
    {
        return $this->threadFormHandler;
    }

    /**
     * @param array $data
     * @param MembershipInterface $author
     * @param ModelInterface $forum
     * @return bool
     */
    public function create(array $data, MembershipInterface $author, ModelInterface $forum): bool
    {
        $threadForm = $this->getThreadForm();
        $threadForm->setAuthor($author);
        $threadForm->setForum($forum);

        if (!$threadForm->loadData($data)) {
            return false;
        }
        return $threadForm->create();
    }

    /**
     * @param ModelFormInterface $threadForm
     * @param array $data
     * @return bool
     */
    public function edit(ModelFormInterface $threadForm, array $data): bool
    {
        if (!$threadForm->loadData($data)) {
            return false;
        }
        return $threadForm->edit();
    }

    /**
     * @param ModelInterface $thread
     * @return int|false
     */
    public function delete(ModelInterface $thread)
    {
        return $thread->delete();
    }

    /**
     * @return MovableInterface
     */
    public function getThreadMover(): MovableInterface
    {
        return $this->threadMoverHandler;
    }

    /**
     * @param MovableInterface $threadMover
     * @param ModelInterface $forum
     * @return bool
     */
    public function move(MovableInterface $threadMover, ModelInterface $forum): bool
    {
        $threadMover->setForum($forum);

        return $threadMover->move();
    }
}
