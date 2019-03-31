<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\BookmarkingInterface;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\LockableInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MovableInterface;
use bizley\podium\api\interfaces\PinnableInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\SubscribingInterface;
use bizley\podium\api\interfaces\ThreadInterface;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

/**
 * Class Thread
 * @package bizley\podium\api\base
 */
class Thread extends PodiumComponent implements ThreadInterface
{
    /**
     * @var string|array|ModelInterface thread handler
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $modelHandler = \bizley\podium\api\models\thread\Thread::class;

    /**
     * @var string|array|CategorisedFormInterface thread form handler
     * Component ID, class, configuration array, or instance of CategorisedFormInterface.
     */
    public $formHandler = \bizley\podium\api\models\thread\ThreadForm::class;

    /**
     * @var string|array|SubscribingInterface subscribing handler
     * Component ID, class, configuration array, or instance of SubscribingInterface.
     */
    public $subscriberHandler = \bizley\podium\api\models\thread\ThreadSubscriber::class;

    /**
     * @var string|array|BookmarkingInterface bookmarking handler
     * Component ID, class, configuration array, or instance of BookmarkingInterface.
     */
    public $bookmarkerHandler = \bizley\podium\api\models\thread\ThreadBookmarker::class;

    /**
     * @var string|array|RemoverInterface thread remover handler
     * Component ID, class, configuration array, or instance of RemoverInterface.
     */
    public $removerHandler = \bizley\podium\api\models\thread\ThreadRemover::class;

    /**
     * @var string|array|ArchiverInterface thread archiver handler
     * Component ID, class, configuration array, or instance of ArchivableInterface.
     */
    public $archiverHandler = \bizley\podium\api\models\thread\ThreadArchiver::class;

    /**
     * @var string|array|MovableInterface thread mover handler
     * Component ID, class, configuration array, or instance of MovableInterface.
     */
    public $moverHandler = \bizley\podium\api\models\thread\ThreadMover::class;

    /**
     * @var string|array|LockableInterface thread locker handler
     * Component ID, class, configuration array, or instance of LockableInterface.
     */
    public $lockerHandler = \bizley\podium\api\models\thread\ThreadLocker::class;

    /**
     * @var string|array|PinnableInterface thread mover handler
     * Component ID, class, configuration array, or instance of PinnableInterface.
     */
    public $pinnerHandler = \bizley\podium\api\models\thread\ThreadPinner::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->modelHandler = Instance::ensure($this->modelHandler, ModelInterface::class);
        $this->formHandler = Instance::ensure($this->formHandler, CategorisedFormInterface::class);
        $this->subscriberHandler = Instance::ensure($this->subscriberHandler, SubscribingInterface::class);
        $this->bookmarkerHandler = Instance::ensure($this->bookmarkerHandler, BookmarkingInterface::class);
        $this->removerHandler = Instance::ensure($this->removerHandler, RemoverInterface::class);
        $this->archiverHandler = Instance::ensure($this->archiverHandler, ArchiverInterface::class);
        $this->moverHandler = Instance::ensure($this->moverHandler, MovableInterface::class);
        $this->lockerHandler = Instance::ensure($this->lockerHandler, LockableInterface::class);
        $this->pinnerHandler = Instance::ensure($this->pinnerHandler, PinnableInterface::class);
    }

    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getById(int $id): ?ModelInterface
    {
        $threadClass = $this->modelHandler;

        return $threadClass::findById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getAll(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $threadClass = $this->modelHandler;

        return $threadClass::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @param int|null $id
     * @return CategorisedFormInterface|null
     */
    public function getForm(?int $id = null): ?CategorisedFormInterface
    {
        $handler = $this->formHandler;

        if ($id === null) {
            return new $handler;
        }

        return $handler::findById($id);
    }

    /**
     * Creates thread.
     * @param array $data
     * @param MembershipInterface $author
     * @param ModelInterface $forum
     * @return PodiumResponse
     */
    public function create(array $data, MembershipInterface $author, ModelInterface $forum): PodiumResponse
    {
        /* @var $threadForm CategorisedFormInterface */
        $threadForm = $this->getForm();

        $threadForm->setAuthor($author);
        $threadForm->setForum($forum);

        if (!$threadForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $threadForm->create();
    }

    /**
     * Updates thread.
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

        $threadForm = $this->getForm((int)$id);

        if ($threadForm === null) {
            throw new ModelNotFoundException('Thread of given ID can not be found.');
        }

        if (!$threadForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $threadForm->edit();
    }

    /**
     * @param int $id
     * @return RemoverInterface|null
     */
    public function getRemover(int $id): ?RemoverInterface
    {
        $handler = $this->removerHandler;

        return $handler::findById($id);
    }

    /**
     * Deletes thread.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function remove(int $id): PodiumResponse
    {
        $threadRemover = $this->getRemover($id);

        if ($threadRemover === null) {
            throw new ModelNotFoundException('Thread of given ID can not be found.');
        }

        return $threadRemover->remove();
    }

    /**
     * @param int $id
     * @return MovableInterface|null
     */
    public function getMover(int $id): ?MovableInterface
    {
        $handler = $this->moverHandler;

        return $handler::findById($id);
    }

    /**
     * Moves thread.
     * @param int $id
     * @param ModelInterface $forum
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function move(int $id, ModelInterface $forum): PodiumResponse
    {
        $threadMover = $this->getMover($id);

        if ($threadMover === null) {
            throw new ModelNotFoundException('Thread of given ID can not be found.');
        }

        $threadMover->setForum($forum);

        return $threadMover->move();
    }

    /**
     * @param int $id
     * @return PinnableInterface|null
     */
    public function getPinner(int $id): ?PinnableInterface
    {
        $handler = $this->pinnerHandler;

        return $handler::findById($id);
    }

    /**
     * Pins thread
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function pin(int $id): PodiumResponse
    {
        $threadPinner = $this->getPinner($id);

        if ($threadPinner === null) {
            throw new ModelNotFoundException('Thread of given ID can not be found.');
        }

        return $threadPinner->pin();
    }

    /**
     * Unpins thread.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function unpin(int $id): PodiumResponse
    {
        $threadPinner = $this->getPinner($id);

        if ($threadPinner === null) {
            throw new ModelNotFoundException('Thread of given ID can not be found.');
        }

        return $threadPinner->unpin();
    }

    /**
     * @param int $id
     * @return LockableInterface|null
     */
    public function getLocker(int $id): ?LockableInterface
    {
        $handler = $this->lockerHandler;

        return $handler::findById($id);
    }

    /**
     * Locks thread.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function lock(int $id): PodiumResponse
    {
        $threadLocker = $this->getLocker($id);

        if ($threadLocker === null) {
            throw new ModelNotFoundException('Thread of given ID can not be found.');
        }

        return $threadLocker->lock();
    }

    /**
     * Unlocks thread.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function unlock(int $id): PodiumResponse
    {
        $threadLocker = $this->getLocker($id);

        if ($threadLocker === null) {
            throw new ModelNotFoundException('Thread of given ID can not be found.');
        }

        return $threadLocker->unlock();
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
     * Archives thread.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function archive(int $id): PodiumResponse
    {
        $threadArchiver = $this->getArchiver($id);

        if ($threadArchiver === null) {
            throw new ModelNotFoundException('Thread of given ID can not be found.');
        }

        return $threadArchiver->archive();
    }

    /**
     * Revives thread.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function revive(int $id): PodiumResponse
    {
        $threadArchiver = $this->getArchiver($id);

        if ($threadArchiver === null) {
            throw new ModelNotFoundException('Thread of given ID can not be found.');
        }

        return $threadArchiver->revive();
    }

    /**
     * @return SubscribingInterface
     */
    public function getSubscriber(): SubscribingInterface
    {
        return new $this->subscriberHandler;
    }

    /**
     * Subscribes to a thread.
     * @param MembershipInterface $member
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function subscribe(MembershipInterface $member, ModelInterface $thread): PodiumResponse
    {
        $subscribing = $this->getSubscriber();

        $subscribing->setMember($member);
        $subscribing->setThread($thread);

        return $subscribing->subscribe();
    }

    /**
     * Unsubscribes from a thread.
     * @param MembershipInterface $member
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function unsubscribe(MembershipInterface $member, ModelInterface $thread): PodiumResponse
    {
        $subscribing = $this->getSubscriber();

        $subscribing->setMember($member);
        $subscribing->setThread($thread);

        return $subscribing->unsubscribe();
    }

    /**
     * @return BookmarkingInterface
     */
    public function getBookmarker(): BookmarkingInterface
    {
        return new $this->bookmarkerHandler;
    }

    /**
     * Marks last seen post in a thread.
     * @param MembershipInterface $member
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function mark(MembershipInterface $member, ModelInterface $post): PodiumResponse
    {
        $bookmarking = $this->getBookmarker();

        $bookmarking->setMember($member);
        $bookmarking->setPost($post);

        return $bookmarking->mark();
    }
}
