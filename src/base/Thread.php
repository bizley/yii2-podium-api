<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\BookmarkerInterface;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\LockerInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\interfaces\PinnerInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\SubscriberInterface;
use bizley\podium\api\interfaces\ThreadInterface;
use bizley\podium\api\models\thread\ThreadArchiver;
use bizley\podium\api\models\thread\ThreadBookmarker;
use bizley\podium\api\models\thread\ThreadForm;
use bizley\podium\api\models\thread\ThreadLocker;
use bizley\podium\api\models\thread\ThreadMover;
use bizley\podium\api\models\thread\ThreadPinner;
use bizley\podium\api\models\thread\ThreadRemover;
use bizley\podium\api\services\thread\ThreadSubscriber;
use yii\base\Component;
use yii\base\InvalidConfigException;
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
final class Thread extends Component implements ThreadInterface
{
    /**
     * @var string|array|ModelInterface thread handler
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $modelConfig = \bizley\podium\api\models\thread\Thread::class;

    /**
     * @var string|array|CategorisedFormInterface thread form handler
     * Component ID, class, configuration array, or instance of CategorisedFormInterface.
     */
    public $formConfig = ThreadForm::class;

    /**
     * @var string|array|SubscriberInterface thread subscriber handler
     * Component ID, class, configuration array, or instance of SubscriberInterface.
     */
    public $subscriberConfig = ThreadSubscriber::class;

    /**
     * @var string|array|BookmarkerInterface thread bookmarker handler
     * Component ID, class, configuration array, or instance of BookmarkerInterface.
     */
    public $bookmarkerConfig = ThreadBookmarker::class;

    /**
     * @var string|array|RemoverInterface thread remover handler
     * Component ID, class, configuration array, or instance of RemoverInterface.
     */
    public $removerConfig = ThreadRemover::class;

    /**
     * @var string|array|ArchiverInterface thread archiver handler
     * Component ID, class, configuration array, or instance of ArchivableInterface.
     */
    public $archiverConfig = ThreadArchiver::class;

    /**
     * @var string|array|MoverInterface thread mover handler
     * Component ID, class, configuration array, or instance of MovableInterface.
     */
    public $moverConfig = ThreadMover::class;

    /**
     * @var string|array|LockerInterface thread locker handler
     * Component ID, class, configuration array, or instance of LockerInterface.
     */
    public $lockerConfig = ThreadLocker::class;

    /**
     * @var string|array|PinnerInterface thread pinner handler
     * Component ID, class, configuration array, or instance of PinnerInterface.
     */
    public $pinnerConfig = ThreadPinner::class;

    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getById(int $id): ?ModelInterface
    {
        /** @var ModelInterface $threadClass */
        $threadClass = Instance::ensure($this->modelConfig, ModelInterface::class);
        return $threadClass::findById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getAll(DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        /** @var ModelInterface $threadClass */
        $threadClass = Instance::ensure($this->modelConfig, ModelInterface::class);
        return $threadClass::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @param int|null $id
     * @return CategorisedFormInterface|null
     */
    public function getForm(int $id = null): ?CategorisedFormInterface
    {
        /** @var CategorisedFormInterface $handler */
        $handler = Instance::ensure($this->formConfig, CategorisedFormInterface::class);
        if ($id === null) {
            return $handler;
        }
        /** @var CategorisedFormInterface|null $findModel */
        $findModel = $handler::findById($id);
        return $findModel;
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
        /** @var CategorisedFormInterface $threadForm */
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
     * @return RemoverInterface
     * @throws InvalidConfigException
     */
    public function getRemover(): RemoverInterface
    {
        /** @var RemoverInterface $handler */
        $handler = Instance::ensure($this->removerConfig, RemoverInterface::class);
        return $handler;
    }

    /**
     * Deletes thread.
     * @param int $id
     * @return PodiumResponse
     * @throws InvalidConfigException
     */
    public function remove(int $id): PodiumResponse
    {
        return $this->getRemover()->remove($id);
    }

    /**
     * @param int $id
     * @return MoverInterface|null
     */
    public function getMover(int $id): ?MoverInterface
    {
        /** @var MoverInterface $handler */
        $handler = Instance::ensure($this->moverConfig, MoverInterface::class);
        /** @var MoverInterface|null $mover */
        $mover = $handler::findById($id);
        return $mover;
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
        $threadMover->prepareForum($forum);
        return $threadMover->move();
    }

    /**
     * @param int $id
     * @return PinnerInterface|null
     */
    public function getPinner(int $id): ?PinnerInterface
    {
        /** @var PinnerInterface $handler */
        $handler = Instance::ensure($this->pinnerConfig, PinnerInterface::class);
        /** @var PinnerInterface|null $pinner */
        $pinner = $handler::findById($id);
        return $pinner;
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
     * @return LockerInterface|null
     */
    public function getLocker(int $id): ?LockerInterface
    {
        /** @var LockerInterface $handler */
        $handler = Instance::ensure($this->lockerConfig, LockerInterface::class);
        /** @var LockerInterface|null $locker */
        $locker = $handler::findById($id);
        return $locker;
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
        /** @var ArchiverInterface $handler */
        $handler = Instance::ensure($this->archiverConfig, ArchiverInterface::class);
        /** @var ArchiverInterface|null $archiver */
        $archiver = $handler::findById($id);
        return $archiver;
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
     * @return SubscriberInterface
     */
    public function getSubscriber(): SubscriberInterface
    {
        /** @var SubscriberInterface $subscriber */
        $subscriber = Instance::ensure($this->subscriberConfig, SubscriberInterface::class);
        return $subscriber;
    }

    /**
     * Subscribes to a thread.
     * @param MembershipInterface $member
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function subscribe(MembershipInterface $member, ModelInterface $thread): PodiumResponse
    {
        return $this->getSubscriber()->subscribe($member, $thread);
    }

    /**
     * Unsubscribes from a thread.
     * @param MembershipInterface $member
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function unsubscribe(MembershipInterface $member, ModelInterface $thread): PodiumResponse
    {
        return $this->getSubscriber()->unsubscribe($member, $thread);
    }

    /**
     * @return BookmarkerInterface
     */
    public function getBookmarker(): BookmarkerInterface
    {
        /** @var BookmarkerInterface $bookmarker */
        $bookmarker = Instance::ensure($this->bookmarkerConfig, BookmarkerInterface::class);
        return $bookmarker;
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
