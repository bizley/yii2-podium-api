<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ForumModelInterface;
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
     * @var string|array|ForumModelInterface
     * Component ID, class, configuration array, or instance of ForumModelInterface.
     */
    public $forumHandler = \bizley\podium\api\models\Forum::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->forumHandler = Instance::ensure($this->forumHandler, ForumModelInterface::class);
    }

    /**
     * @return ForumModelInterface
     */
    public function getForumModel(): ForumModelInterface
    {
        return $this->forumHandler;
    }

    /**
     * @param int $id
     * @return ForumModelInterface|null
     */
    public function getForumById(int $id): ?ForumModelInterface
    {
        $forumModel = $this->getForumModel();
        return $forumModel::findForumById($id);
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
        return $forumModel::findForums($filter, $sort, $pagination);
    }
}
