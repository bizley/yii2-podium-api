<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

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
     * @var string|array|ThreadModelInterface
     * Component ID, class, configuration array, or instance of ThreadModelInterface.
     */
    public $threadHandler = \bizley\podium\api\models\Thread::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->threadHandler = Instance::ensure($this->threadHandler, ThreadModelInterface::class);
    }

    /**
     * @return ThreadModelInterface
     */
    public function getThreadModel(): ThreadModelInterface
    {
        return $this->threadHandler;
    }

    /**
     * @param int $id
     * @return ThreadModelInterface|null
     */
    public function getThreadById(int $id): ?ThreadModelInterface
    {
        return $this->getThreadModel()->findThreadById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null $sort
     * @param null $pagination
     * @return DataProviderInterface
     */
    public function getThreads(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        return $this->getThreadModel()->findThreads($filter, $sort, $pagination);
    }
}
