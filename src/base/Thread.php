<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ArchivableInterface;
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
    public $threadHandler = \bizley\podium\api\models\thread\Thread::class;

    /**
     * @var string|array|CategorisedFormInterface thread form handler
     * Component ID, class, configuration array, or instance of CategorisedFormInterface.
     */
    public $threadFormHandler = \bizley\podium\api\models\thread\ThreadForm::class;

    /**
     * @var string|array|SubscribingInterface subscribing handler
     * Component ID, class, configuration array, or instance of SubscribingInterface.
     */
    public $subscribingHandler = \bizley\podium\api\models\thread\Subscribing::class;

    /**
     * @var string|array|BookmarkingInterface bookmarking handler
     * Component ID, class, configuration array, or instance of BookmarkingInterface.
     */
    public $bookmarkingHandler = \bizley\podium\api\models\thread\Bookmarking::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->threadHandler = Instance::ensure($this->threadHandler, ModelInterface::class);
        $this->threadFormHandler = Instance::ensure($this->threadFormHandler, CategorisedFormInterface::class);
        $this->subscribingHandler = Instance::ensure($this->subscribingHandler, SubscribingInterface::class);
        $this->bookmarkingHandler = Instance::ensure($this->bookmarkingHandler, BookmarkingInterface::class);
    }

    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getThreadById(int $id): ?ModelInterface
    {
        $threadClass = $this->threadHandler;

        return $threadClass::findById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getThreads(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $threadClass = $this->threadHandler;

        return $threadClass::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @param int|null $id
     * @return CategorisedFormInterface|null
     */
    public function getThreadForm(?int $id = null): ?CategorisedFormInterface
    {
        $handler = $this->threadFormHandler;

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
        $threadForm = $this->getThreadForm();

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

        $threadForm = $this->getThreadForm((int)$id);

        if ($threadForm === null) {
            throw new ModelNotFoundException('Thread of given ID can not be found.');
        }

        if (!$threadForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $threadForm->edit();
    }

    /**
     * Deletes thread.
     * @param RemoverInterface $threadRemover
     * @return PodiumResponse
     */
    public function remove(RemoverInterface $threadRemover): PodiumResponse
    {
        return $threadRemover->remove();
    }

    /**
     * Moves thread.
     * @param MovableInterface $threadMover
     * @param ModelInterface $forum
     * @return PodiumResponse
     */
    public function move(MovableInterface $threadMover, ModelInterface $forum): PodiumResponse
    {
        $threadMover->setForum($forum);

        return $threadMover->move();
    }

    /**
     * Pins thread
     * @param PinnableInterface $threadPinner
     * @return PodiumResponse
     */
    public function pin(PinnableInterface $threadPinner): PodiumResponse
    {
        return $threadPinner->pin();
    }

    /**
     * Unpins thread.
     * @param PinnableInterface $threadPinner
     * @return PodiumResponse
     */
    public function unpin(PinnableInterface $threadPinner): PodiumResponse
    {
        return $threadPinner->unpin();
    }

    /**
     * Locks thread.
     * @param LockableInterface $threadLocker
     * @return PodiumResponse
     */
    public function lock(LockableInterface $threadLocker): PodiumResponse
    {
        return $threadLocker->lock();
    }

    /**
     * Unlocks thread.
     * @param LockableInterface $threadLocker
     * @return PodiumResponse
     */
    public function unlock(LockableInterface $threadLocker): PodiumResponse
    {
        return $threadLocker->unlock();
    }

    /**
     * Archives thread.
     * @param ArchivableInterface $threadArchiver
     * @return PodiumResponse
     */
    public function archive(ArchivableInterface $threadArchiver): PodiumResponse
    {
        return $threadArchiver->archive();
    }

    /**
     * Revives thread.
     * @param ArchivableInterface $threadArchiver
     * @return PodiumResponse
     */
    public function revive(ArchivableInterface $threadArchiver): PodiumResponse
    {
        return $threadArchiver->revive();
    }

    /**
     * @return SubscribingInterface
     */
    public function getSubscribing(): SubscribingInterface
    {
        return new $this->subscribingHandler;
    }

    /**
     * Subscribes to a thread.
     * @param MembershipInterface $member
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function subscribe(MembershipInterface $member, ModelInterface $thread): PodiumResponse
    {
        $subscribing = $this->getSubscribing();

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
        $subscribing = $this->getSubscribing();

        $subscribing->setMember($member);
        $subscribing->setThread($thread);

        return $subscribing->unsubscribe();
    }

    /**
     * @return BookmarkingInterface
     */
    public function getBookmarking(): BookmarkingInterface
    {
        return new $this->bookmarkingHandler;
    }

    /**
     * Marks last seen post in a thread.
     * @param MembershipInterface $member
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function mark(MembershipInterface $member, ModelInterface $post): PodiumResponse
    {
        $bookmarking = $this->getBookmarking();

        $bookmarking->setMember($member);
        $bookmarking->setPost($post);

        return $bookmarking->mark();
    }
}
