<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ArchivableInterface;
use bizley\podium\api\interfaces\BookmarkingInterface;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\LockableInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MovableInterface;
use bizley\podium\api\interfaces\PinnableInterface;
use bizley\podium\api\interfaces\RemovableInterface;
use bizley\podium\api\interfaces\SubscribingInterface;
use bizley\podium\api\interfaces\ThreadInterface;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\di\Instance;

/**
 * Class Thread
 * @package bizley\podium\api\base
 *
 * @property CategorisedFormInterface $threadForm
 * @property ModelInterface $threadModel
 * @property MovableInterface $threadMover
 */
class Thread extends PodiumComponent implements ThreadInterface
{
    /**
     * @var string|array|ModelInterface
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $threadHandler = \bizley\podium\api\models\thread\Thread::class;

    /**
     * @var string|array|CategorisedFormInterface
     * Component ID, class, configuration array, or instance of CategorisedFormInterface.
     */
    public $threadFormHandler = \bizley\podium\api\models\thread\ThreadForm::class;

    /**
     * @var string|array|SubscribingInterface
     * Component ID, class, configuration array, or instance of SubscribingInterface.
     */
    public $subscribingHandler = \bizley\podium\api\models\thread\Subscribing::class;

    /**
     * @var string|array|BookmarkingInterface
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
     * @param null $sort
     * @param null $pagination
     * @return DataProviderInterface
     */
    public function getThreads(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $threadClass = $this->threadHandler;
        return $threadClass::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @return CategorisedFormInterface
     */
    public function getThreadForm(): CategorisedFormInterface
    {
        return new $this->threadFormHandler;
    }

    /**
     * @param array $data
     * @param MembershipInterface $author
     * @param ModelInterface $forum
     * @return bool
     */
    public function create(array $data, MembershipInterface $author, ModelInterface $forum): bool
    {
        $threadForm = $this->getThreadForm();
        $threadForm->setAuthor($author);
        $threadForm->setForum($forum);

        if (!$threadForm->loadData($data)) {
            return false;
        }
        return $threadForm->create();
    }

    /**
     * @param ModelFormInterface $threadForm
     * @param array $data
     * @return bool
     */
    public function edit(ModelFormInterface $threadForm, array $data): bool
    {
        if (!$threadForm->loadData($data)) {
            return false;
        }
        return $threadForm->edit();
    }

    /**
     * @param RemovableInterface $threadRemover
     * @return bool
     */
    public function remove(RemovableInterface $threadRemover): bool
    {
        return $threadRemover->remove();
    }

    /**
     * @param MovableInterface $threadMover
     * @param ModelInterface $forum
     * @return bool
     */
    public function move(MovableInterface $threadMover, ModelInterface $forum): bool
    {
        $threadMover->setForum($forum);

        return $threadMover->move();
    }

    /**
     * @param PinnableInterface $threadPinner
     * @return bool
     */
    public function pin(PinnableInterface $threadPinner): bool
    {
        return $threadPinner->pin();
    }

    /**
     * @param PinnableInterface $threadPinner
     * @return bool
     */
    public function unpin(PinnableInterface $threadPinner): bool
    {
        return $threadPinner->unpin();
    }

    /**
     * @param LockableInterface $threadLocker
     * @return bool
     */
    public function lock(LockableInterface $threadLocker): bool
    {
        return $threadLocker->lock();
    }

    /**
     * @param LockableInterface $threadLocker
     * @return bool
     */
    public function unlock(LockableInterface $threadLocker): bool
    {
        return $threadLocker->unlock();
    }

    /**
     * @param ArchivableInterface $threadArchiver
     * @return bool
     */
    public function archive(ArchivableInterface $threadArchiver): bool
    {
        return $threadArchiver->archive();
    }

    /**
     * @param ArchivableInterface $threadArchiver
     * @return bool
     */
    public function revive(ArchivableInterface $threadArchiver): bool
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
     * @param MembershipInterface $member
     * @param ModelInterface $thread
     * @return bool
     */
    public function subscribe(MembershipInterface $member, ModelInterface $thread): bool
    {
        $subscribing = $this->getSubscribing();
        $subscribing->setMember($member);
        $subscribing->setThread($thread);

        return $subscribing->subscribe();
    }

    /**
     * @param MembershipInterface $member
     * @param ModelInterface $thread
     * @return bool
     */
    public function unsubscribe(MembershipInterface $member, ModelInterface $thread): bool
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
     * @param MembershipInterface $member
     * @param ModelInterface $post
     * @return bool
     */
    public function mark(MembershipInterface $member, ModelInterface $post): bool
    {
        $bookmarking = $this->getBookmarking();
        $bookmarking->setMember($member);
        $bookmarking->setPost($post);

        return $bookmarking->mark();
    }
}
