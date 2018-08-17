<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ArchivableInterface;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\LikingInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MovableInterface;
use bizley\podium\api\interfaces\PostInterface;
use bizley\podium\api\interfaces\RemovableInterface;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\di\Instance;

/**
 * Class Post
 * @package bizley\podium\api\base
 */
class Post extends PodiumComponent implements PostInterface
{
    /**
     * @var string|array|ModelInterface
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $postHandler = \bizley\podium\api\models\post\Post::class;

    /**
     * @var string|array|CategorisedFormInterface
     * Component ID, class, configuration array, or instance of CategorisedFormInterface.
     */
    public $postFormHandler = \bizley\podium\api\models\post\PostForm::class;

    /**
     * @var string|array|LikingInterface
     * Component ID, class, configuration array, or instance of LikingInterface.
     */
    public $likingHandler = \bizley\podium\api\models\post\Liking::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->postHandler = Instance::ensure($this->postHandler, ModelInterface::class);
        $this->postFormHandler = Instance::ensure($this->postFormHandler, CategorisedFormInterface::class);
        $this->likingHandler = Instance::ensure($this->likingHandler, LikingInterface::class);
    }

    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getPostById(int $id): ?ModelInterface
    {
        $postClass = $this->postHandler;
        return $postClass::findById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null $sort
     * @param null $pagination
     * @return DataProviderInterface
     */
    public function getPosts(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $postClass = $this->postHandler;
        return $postClass::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @return CategorisedFormInterface
     */
    public function getPostForm(): CategorisedFormInterface
    {
        return new $this->postFormHandler;
    }

    /**
     * @param array $data
     * @param MembershipInterface $author
     * @param ModelInterface $thread
     * @return bool
     */
    public function create(array $data, MembershipInterface $author, ModelInterface $thread): bool
    {
        $postForm = $this->getPostForm();
        $postForm->setAuthor($author);
        $postForm->setThread($thread);

        if (!$postForm->loadData($data)) {
            return false;
        }
        return $postForm->create();
    }

    /**
     * @param ModelFormInterface $postForm
     * @param array $data
     * @return bool
     */
    public function edit(ModelFormInterface $postForm, array $data): bool
    {
        if (!$postForm->loadData($data)) {
            return false;
        }
        return $postForm->edit();
    }

    /**
     * @param RemovableInterface $postRemover
     * @return bool
     */
    public function remove(RemovableInterface $postRemover): bool
    {
        return $postRemover->remove();
    }

    /**
     * @param MovableInterface $postMover
     * @param ModelInterface $thread
     * @return bool
     */
    public function move(MovableInterface $postMover, ModelInterface $thread): bool
    {
        $postMover->setThread($thread);

        return $postMover->move();
    }

    /**
     * @param ArchivableInterface $postArchiver
     * @return bool
     */
    public function archive(ArchivableInterface $postArchiver): bool
    {
        return $postArchiver->archive();
    }

    /**
     * @param ArchivableInterface $postArchiver
     * @return bool
     */
    public function revive(ArchivableInterface $postArchiver): bool
    {
        return $postArchiver->revive();
    }

    /**
     * @return LikingInterface
     */
    public function getLiking(): LikingInterface
    {
        return new $this->likingHandler;
    }

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $post
     * @return bool
     */
    public function thumbUp(MembershipInterface $member, ModelInterface $post): bool
    {
        $liking = $this->getLiking();
        $liking->setMember($member);
        $liking->setPost($post);
        return $liking->thumbUp();
    }

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $post
     * @return bool
     */
    public function thumbDown(MembershipInterface $member, ModelInterface $post): bool
    {
        $liking = $this->getLiking();
        $liking->setMember($member);
        $liking->setPost($post);
        return $liking->thumbDown();
    }

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $post
     * @return bool
     */
    public function thumbReset(MembershipInterface $member, ModelInterface $post): bool
    {
        $liking = $this->getLiking();
        $liking->setMember($member);
        $liking->setPost($post);
        return $liking->thumbReset();
    }
}
