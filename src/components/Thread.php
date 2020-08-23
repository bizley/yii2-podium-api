<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\ars\ThreadActiveRecord;
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
use yii\base\NotSupportedException;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
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
    public function getById(int $id): ?ThreadActiveRecord
    {
        /** @var ThreadRepository $thread */
        $thread = Instance::ensure($this->repositoryConfig, ThreadRepositoryInterface::class);
        if (!$thread->fetchOne($id)) {
            return null;
        }

        return $thread->getModel();
    }

    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    public function getAll(ActiveDataFilter $filter = null, $sort = null, $pagination = null): ActiveDataProvider
    {
        /** @var ThreadRepository $thread */
        $thread = Instance::ensure($this->repositoryConfig, ThreadRepositoryInterface::class);
        $thread->fetchAll($filter, $sort, $pagination);

        return $thread->getCollection();
    }

    /**
     * @throws InvalidConfigException
     */
    public function getBuilder(): CategorisedBuilderInterface
    {
        /** @var CategorisedBuilderInterface $builder */
        $builder = Instance::ensure($this->builderConfig, CategorisedBuilderInterface::class);

        return $builder;
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

    /**
     * @throws InvalidConfigException
     */
    public function getRemover(): RemoverInterface
    {
        /** @var RemoverInterface $remover */
        $remover = Instance::ensure($this->removerConfig, RemoverInterface::class);

        return $remover;
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

    /**
     * @throws InvalidConfigException
     */
    public function getMover(): MoverInterface
    {
        /** @var MoverInterface $mover */
        $mover = Instance::ensure($this->moverConfig, MoverInterface::class);

        return $mover;
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

    /**
     * @throws InvalidConfigException
     */
    public function getPinner(): PinnerInterface
    {
        /** @var PinnerInterface $pinner */
        $pinner = Instance::ensure($this->pinnerConfig, PinnerInterface::class);

        return $pinner;
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

    /**
     * @throws InvalidConfigException
     */
    public function getLocker(): LockerInterface
    {
        /** @var LockerInterface $locker */
        $locker = Instance::ensure($this->lockerConfig, LockerInterface::class);

        return $locker;
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

    /**
     * @throws InvalidConfigException
     */
    public function getArchiver(): ArchiverInterface
    {
        /** @var ArchiverInterface $archiver */
        $archiver = Instance::ensure($this->archiverConfig, ArchiverInterface::class);

        return $archiver;
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

    /**
     * @throws InvalidConfigException
     */
    public function getSubscriber(): SubscriberInterface
    {
        /** @var SubscriberInterface $subscriber */
        $subscriber = Instance::ensure($this->subscriberConfig, SubscriberInterface::class);

        return $subscriber;
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

    /**
     * @throws InvalidConfigException
     */
    public function getBookmarker(): BookmarkerInterface
    {
        /** @var BookmarkerInterface $bookmarker */
        $bookmarker = Instance::ensure($this->bookmarkerConfig, BookmarkerInterface::class);

        return $bookmarker;
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
