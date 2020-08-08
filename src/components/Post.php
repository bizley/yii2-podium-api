<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\LikerInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\interfaces\PostInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\models\post\PostArchiver;
use bizley\podium\api\models\post\PostForm;
use bizley\podium\api\models\post\PostLiker;
use bizley\podium\api\models\post\PostMover;
use bizley\podium\api\models\post\PostRemover;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

/**
 * Class Post
 * @package bizley\podium\api\base
 */
final class Post extends Component implements PostInterface
{
    /**
     * @var string|array|ModelInterface post handler
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $modelHandler = \bizley\podium\api\models\post\Post::class;

    /**
     * @var string|array|CategorisedFormInterface post form handler
     * Component ID, class, configuration array, or instance of CategorisedFormInterface.
     */
    public $formHandler = PostForm::class;

    /**
     * @var string|array|LikerInterface liking handler
     * Component ID, class, configuration array, or instance of LikerInterface.
     */
    public $likerHandler = PostLiker::class;

    /**
     * @var string|array|RemoverInterface post remover handler
     * Component ID, class, configuration array, or instance of RemoverInterface.
     */
    public $removerHandler = PostRemover::class;

    /**
     * @var string|array|ArchiverInterface post archiver handler
     * Component ID, class, configuration array, or instance of ArchiverInterface.
     */
    public $archiverHandler = PostArchiver::class;

    /**
     * @var string|array|MoverInterface post mover handler
     * Component ID, class, configuration array, or instance of MoverInterface.
     */
    public $moverHandler = PostMover::class;

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

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
        /** @var ModelInterface $postClass */
        $postClass = Instance::ensure($this->modelHandler, ModelInterface::class);
        return $postClass::findById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getAll(DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        /** @var ModelInterface $postClass */
        $postClass = Instance::ensure($this->modelHandler, ModelInterface::class);
        return $postClass::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @param int|null $id
     * @return CategorisedFormInterface|null
     */
    public function getForm(int $id = null): ?CategorisedFormInterface
    {
        /** @var CategorisedFormInterface $handler */
        $handler = Instance::ensure($this->formHandler, CategorisedFormInterface::class);
        if ($id === null) {
            return $handler;
        }
        /** @var CategorisedFormInterface|null $form */
        $form = $handler::findById($id);
        return $form;
    }

    /**
     * Creates post.
     * @param array $data
     * @param MembershipInterface $author
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function create(array $data, MembershipInterface $author, ModelInterface $thread): PodiumResponse
    {
        /** @var CategorisedFormInterface $postForm */
        $postForm = $this->getForm();

        $postForm->setAuthor($author);
        $postForm->setThread($thread);

        if (!$postForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $postForm->create();
    }

    /**
     * Updates post.
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

        $postForm = $this->getForm((int)$id);
        if ($postForm === null) {
            throw new ModelNotFoundException('Post of given ID can not be found.');
        }
        if (!$postForm->loadData($data)) {
            return PodiumResponse::error();
        }
        return $postForm->edit();
    }

    /**
     * @param int $id
     * @return RemoverInterface|null
     */
    public function getRemover(int $id): ?RemoverInterface
    {
        /** @var RemoverInterface $handler */
        $handler = Instance::ensure($this->removerHandler, RemoverInterface::class);
        /** @var RemoverInterface|null $remover */
        $remover = $handler::findById($id);
        return $remover;
    }

    /**
     * Deletes post.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function remove(int $id): PodiumResponse
    {
        $postRemover = $this->getRemover($id);

        if ($postRemover === null) {
            throw new ModelNotFoundException('Post of given ID can not be found.');
        }

        return $postRemover->remove();
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
     * Moves post.
     * @param int $id
     * @param ModelInterface $thread
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function move(int $id, ModelInterface $thread): PodiumResponse
    {
        $postMover = $this->getMover($id);

        if ($postMover === null) {
            throw new ModelNotFoundException('Post of given ID can not be found.');
        }

        $postMover->prepareThread($thread);

        return $postMover->move();
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
     * Archives post.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function archive(int $id): PodiumResponse
    {
        $postArchiver = $this->getArchiver($id);

        if ($postArchiver === null) {
            throw new ModelNotFoundException('Post of given ID can not be found.');
        }

        return $postArchiver->archive();
    }

    /**
     * Revives post.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function revive(int $id): PodiumResponse
    {
        $postArchiver = $this->getArchiver($id);

        if ($postArchiver === null) {
            throw new ModelNotFoundException('Post of given ID can not be found.');
        }

        return $postArchiver->revive();
    }

    /**
     * @return LikerInterface
     */
    public function getLiker(): LikerInterface
    {
        /** @var LikerInterface $liker */
        $liker = Instance::ensure($this->likerHandler, LikerInterface::class);
        return $liker;
    }

    /**
     * Gives post a thumb up.
     * @param MembershipInterface $member
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function thumbUp(MembershipInterface $member, ModelInterface $post): PodiumResponse
    {
        $liking = $this->getLiker();

        $liking->setMember($member);
        $liking->setPost($post);

        return $liking->thumbUp();
    }

    /**
     * Gives post a thumb down.
     * @param MembershipInterface $member
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function thumbDown(MembershipInterface $member, ModelInterface $post): PodiumResponse
    {
        $liking = $this->getLiker();

        $liking->setMember($member);
        $liking->setPost($post);

        return $liking->thumbDown();
    }

    /**
     * Resets post given thumb.
     * @param MembershipInterface $member
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function thumbReset(MembershipInterface $member, ModelInterface $post): PodiumResponse
    {
        $liking = $this->getLiker();

        $liking->setMember($member);
        $liking->setPost($post);

        return $liking->thumbReset();
    }
}
