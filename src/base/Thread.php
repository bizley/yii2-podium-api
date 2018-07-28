<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ModelInterface;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\di\Instance;

/**
 * Class Thread
 * @package bizley\podium\api\base
 */
class Thread extends PodiumComponent
{
    /**
     * @var string|array|ModelInterface
     * Component ID, class, configuration array, or instance of ThreadModelInterface.
     */
    public $threadHandler = \bizley\podium\api\models\Thread::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->threadHandler = Instance::ensure($this->threadHandler, ModelInterface::class);
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
}
