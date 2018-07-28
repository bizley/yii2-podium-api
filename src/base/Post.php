<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\PostModelInterface;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\di\Instance;

/**
 * Class Post
 * @package bizley\podium\api\base
 */
class Post extends PodiumComponent
{
    /**
     * @var string|array|PostModelInterface
     * Component ID, class, configuration array, or instance of PostModelInterface.
     */
    public $postHandler = \bizley\podium\api\models\Post::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->postHandler = Instance::ensure($this->postHandler, PostModelInterface::class);
    }

    /**
     * @return PostModelInterface
     */
    public function getPostModel(): PostModelInterface
    {
        return $this->postHandler;
    }

    /**
     * @param int $id
     * @return PostModelInterface|null
     */
    public function getPostById(int $id): ?PostModelInterface
    {
        $postModel = $this->getPostModel();
        return $postModel::findPostById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null $sort
     * @param null $pagination
     * @return DataProviderInterface
     */
    public function getPosts(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $postModel = $this->getPostModel();
        return $postModel::findPosts($filter, $sort, $pagination);
    }
}
