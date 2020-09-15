<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\interfaces\ActiveRecordRepositoryInterface;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\BookmarkerInterface;
use bizley\podium\api\interfaces\CategorisedBuilderInterface;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\LockerInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\interfaces\PinnerInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\SubscriberInterface;
use bizley\podium\api\interfaces\ThreadInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\repositories\ThreadRepository;
use bizley\podium\api\services\thread\ThreadArchiver;
use bizley\podium\api\services\thread\ThreadBookmarker;
use bizley\podium\api\services\thread\ThreadBuilder;
use bizley\podium\api\services\thread\ThreadLocker;
use bizley\podium\api\services\thread\ThreadMover;
use bizley\podium\api\services\thread\ThreadPinner;
use bizley\podium\api\services\thread\ThreadRemover;
use bizley\podium\api\services\thread\ThreadSubscriber;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\data\Sort;
use yii\db\ActiveRecord;
use yii\di\Instance;

final class Thread extends Component implements ThreadInterface
{
    /**
     * @var string|array|CategorisedBuilderInterface
     */
    public $builderConfig = ThreadBuilder::class;

    /**
     * @var string|array|SubscriberInterface
     */
    public $subscriberConfig = ThreadSubscriber::class;

    /**
     * @var string|array|BookmarkerInterface
     */
    public $bookmarkerConfig = ThreadBookmarker::class;

    /**
     * @var string|array|RemoverInterface
     */
    public $removerConfig = ThreadRemover::class;

    /**
     * @var string|array|ArchiverInterface
     */
    public $archiverConfig = ThreadArchiver::class;

    /**
     * @var string|array|MoverInterface
     */
    public $moverConfig = ThreadMover::class;

    /**
     * @var string|array|LockerInterface
     */
    public $lockerConfig = ThreadLocker::class;

    /**
     * @var string|array|PinnerInterface
     */
    public $pinnerConfig = ThreadPinner::class;

    /**
     * @var string|array|ThreadRepositoryInterface
     */
    public $repositoryConfig = ThreadRepository::class;

    /**
     * @throws InvalidConfigException
     */
    public function getById(int $id): ?ActiveRecord
    {
        /** @var ActiveRecordRepositoryInterface $thread */
        $thread = Instance::ensure($this->repositoryConfig, ActiveRecordRepositoryInterface::class);
        if (!$thread->fetchOne($id)) {
            return null;
        }

        return $thread->getModel();
    }

    /**
     * @param bool|array|Sort|null       $sort
     * @param bool|array|Pagination|null $pagination
     *
     * @throws InvalidConfigException
     */
    public function getAll(ActiveDataFilter $filter = null, $sort = null, $pagination = null): ActiveDataProvider
    {
        /** @var ActiveRecordRepositoryInterface $thread */
        $thread = Instance::ensure($this->repositoryConfig, ActiveRecordRepositoryInterface::class);
        $thread->fetchAll($filter, $sort, $pagination);

        return $thread->getCollection();
    }

    private ?CategorisedBuilderInterface $builder = null;

    /**
     * @throws InvalidConfigException
     */
    public function getBuilder(): CategorisedBuilderInterface
    {
        if (null === $this->builder) {
            /** @var CategorisedBuilderInterface $builder */
            $builder = Instance::ensure($this->builderConfig, CategorisedBuilderInterface::class);
            $this->builder = $builder;
        }

        return $this->builder;
    }

    /**
     * Creates thread.
     *
     * @throws InvalidConfigException
     */
    public function create(
        MemberRepositoryInterface $author,
        ForumRepositoryInterface $forum,
        array $data = []
    ): PodiumResponse {
        return $this->getBuilder()->create($author, $forum, $data);
    }

    /**
     * Updates thread.
     *
     * @throws InvalidConfigException
     */
    public function edit(ThreadRepositoryInterface $thread, array $data = []): PodiumResponse
    {
        return $this->getBuilder()->edit($thread, $data);
    }

    private ?RemoverInterface $remover = null;

    /**
     * @throws InvalidConfigException
     */
    public function getRemover(): RemoverInterface
    {
        if (null === $this->remover) {
            /** @var RemoverInterface $remover */
            $remover = Instance::ensure($this->removerConfig, RemoverInterface::class);
            $this->remover = $remover;
        }

        return $this->remover;
    }

    /**
     * Deletes thread.
     *
     * @throws InvalidConfigException
     */
    public function remove(ThreadRepositoryInterface $thread): PodiumResponse
    {
        return $this->getRemover()->remove($thread);
    }

    private ?MoverInterface $mover = null;

    /**
     * @throws InvalidConfigException
     */
    public function getMover(): MoverInterface
    {
        if (null === $this->mover) {
            /** @var MoverInterface $mover */
            $mover = Instance::ensure($this->moverConfig, MoverInterface::class);
            $this->mover = $mover;
        }

        return $this->mover;
    }

    /**
     * Moves thread.
     *
     * @throws InvalidConfigException
     */
    public function move(ThreadRepositoryInterface $thread, ForumRepositoryInterface $forum): PodiumResponse
    {
        return $this->getMover()->move($thread, $forum);
    }

    private ?PinnerInterface $pinner = null;

    /**
     * @throws InvalidConfigException
     */
    public function getPinner(): PinnerInterface
    {
        if (null === $this->pinner) {
            /** @var PinnerInterface $pinner */
            $pinner = Instance::ensure($this->pinnerConfig, PinnerInterface::class);
            $this->pinner = $pinner;
        }

        return $this->pinner;
    }

    /**
     * Pins thread.
     *
     * @throws InvalidConfigException
     */
    public function pin(ThreadRepositoryInterface $thread): PodiumResponse
    {
        return $this->getPinner()->pin($thread);
    }

    /**
     * Unpins thread.
     *
     * @throws InvalidConfigException
     */
    public function unpin(ThreadRepositoryInterface $thread): PodiumResponse
    {
        return $this->getPinner()->unpin($thread);
    }

    private ?LockerInterface $locker = null;

    /**
     * @throws InvalidConfigException
     */
    public function getLocker(): LockerInterface
    {
        if (null === $this->locker) {
            /** @var LockerInterface $locker */
            $locker = Instance::ensure($this->lockerConfig, LockerInterface::class);
            $this->locker = $locker;
        }

        return $this->locker;
    }

    /**
     * Locks thread.
     *
     * @throws InvalidConfigException
     */
    public function lock(ThreadRepositoryInterface $thread): PodiumResponse
    {
        return $this->getLocker()->lock($thread);
    }

    /**
     * Unlocks thread.
     *
     * @throws InvalidConfigException
     */
    public function unlock(ThreadRepositoryInterface $thread): PodiumResponse
    {
        return $this->getLocker()->unlock($thread);
    }

    private ?ArchiverInterface $archiver = null;

    /**
     * @throws InvalidConfigException
     */
    public function getArchiver(): ArchiverInterface
    {
        if (null === $this->archiver) {
            /** @var ArchiverInterface $archiver */
            $archiver = Instance::ensure($this->archiverConfig, ArchiverInterface::class);
            $this->archiver = $archiver;
        }

        return $this->archiver;
    }

    /**
     * Archives thread.
     *
     * @throws InvalidConfigException
     */
    public function archive(ThreadRepositoryInterface $thread): PodiumResponse
    {
        return $this->getArchiver()->archive($thread);
    }

    /**
     * Revives thread.
     *
     * @throws InvalidConfigException
     */
    public function revive(ThreadRepositoryInterface $thread): PodiumResponse
    {
        return $this->getArchiver()->revive($thread);
    }

    private ?SubscriberInterface $subscriber = null;

    /**
     * @throws InvalidConfigException
     */
    public function getSubscriber(): SubscriberInterface
    {
        if (null === $this->subscriber) {
            /** @var SubscriberInterface $subscriber */
            $subscriber = Instance::ensure($this->subscriberConfig, SubscriberInterface::class);
            $this->subscriber = $subscriber;
        }

        return $this->subscriber;
    }

    /**
     * Subscribes to a thread.
     *
     * @throws InvalidConfigException
     */
    public function subscribe(ThreadRepositoryInterface $thread, MemberRepositoryInterface $member): PodiumResponse
    {
        return $this->getSubscriber()->subscribe($thread, $member);
    }

    /**
     * Unsubscribes from a thread.
     *
     * @throws InvalidConfigException
     */
    public function unsubscribe(ThreadRepositoryInterface $thread, MemberRepositoryInterface $member): PodiumResponse
    {
        return $this->getSubscriber()->unsubscribe($thread, $member);
    }

    private ?BookmarkerInterface $bookmarker = null;

    /**
     * @throws InvalidConfigException
     */
    public function getBookmarker(): BookmarkerInterface
    {
        if (null === $this->bookmarker) {
            /** @var BookmarkerInterface $bookmarker */
            $bookmarker = Instance::ensure($this->bookmarkerConfig, BookmarkerInterface::class);
            $this->bookmarker = $bookmarker;
        }

        return $this->bookmarker;
    }

    /**
     * Marks last seen post in a thread.
     *
     * @throws InvalidConfigException
     */
    public function mark(PostRepositoryInterface $post, MemberRepositoryInterface $member): PodiumResponse
    {
        return $this->getBookmarker()->mark($post, $member);
    }
}
