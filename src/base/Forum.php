<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ModelInterface;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\di\Instance;

/**
 * Class Forum
 * @package bizley\podium\api\base
 */
class Forum extends PodiumComponent
{
    /**
     * @var string|array|ModelInterface
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $forumHandler = \bizley\podium\api\models\forum\Forum::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->forumHandler = Instance::ensure($this->forumHandler, ModelInterface::class);
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
     * @param null $sort
     * @param null $pagination
     * @return DataProviderInterface
     */
    public function getForums(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $forumModel = $this->getForumModel();
        return $forumModel::findByFilter($filter, $sort, $pagination);
    }
}
