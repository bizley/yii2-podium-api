<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\ars\ThreadActiveRecord;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\ActiveRecordThreadRepositoryInterface;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\BookmarkerInterface;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\LockerInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\interfaces\PinnerInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\SubscriberInterface;
use bizley\podium\api\interfaces\ThreadInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\models\thread\ThreadForm;
use bizley\podium\api\repositories\ThreadRepository;
use bizley\podium\api\services\thread\ThreadArchiver;
use bizley\podium\api\services\thread\ThreadBookmarker;
use bizley\podium\api\services\thread\ThreadLocker;
use bizley\podium\api\services\thread\ThreadMover;
use bizley\podium\api\services\thread\ThreadPinner;
use bizley\podium\api\services\thread\ThreadRemover;
use bizley\podium\api\services\thread\ThreadSubscriber;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

final class Thread extends Component implements ThreadInterface
{
    /**
     * @var string|array|ModelInterface
     */
    public $modelConfig = \bizley\podium\api\models\thread\Thread::class;

    /**
     * @var string|array|CategorisedFormInterface
     */
    public $formConfig = ThreadForm::class;

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
        /** @var ActiveRecordThreadRepositoryInterface $thread */
        $thread = Instance::ensure($this->repositoryConfig, ActiveRecordThreadRepositoryInterface::class);
        if (!$thread->fetchOne($id)) {
            return null;
        }

        return $thread->getModel();
    }

    /**
     * @throws InvalidConfigException
     */
    public function getAll($filter = null, $sort = null, $pagination = null): ActiveDataProvider
    {
        /** @var ActiveRecordThreadRepositoryInterface $thread */
        $thread = Instance::ensure($this->repositoryConfig, ActiveRecordThreadRepositoryInterface::class);
        $thread->fetchAll($filter, $sort, $pagination);

        return $thread->getCollection();
    }

    /**
     * @throws InvalidConfigException
     */
    public function getForm(int $id = null): ?CategorisedFormInterface
    {
        /** @var CategorisedFormInterface $handler */
        $handler = Instance::ensure($this->formConfig, CategorisedFormInterface::class);
        if (null === $id) {
            return $handler;
        }
        /** @var CategorisedFormInterface|null $findModel */
        $findModel = $handler::findById($id);

        return $findModel;
    }

    /**
     * Creates thread.
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
     *
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function edit(array $data): PodiumResponse
    {
        $id = ArrayHelper::remove($data, 'id');
        if (null === $id) {
            throw new InsufficientDataException('ID key is missing.');
        }

        $threadForm = $this->getForm((int) $id);
        if (null === $threadForm) {
            throw new ModelNotFoundException('Thread of given ID can not be found.');
        }
        if (!$threadForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $threadForm->edit();
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
    public function remove(int $id): PodiumResponse
    {
        return $this->getRemover()->remove($id);
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
    public function move(int $id, ForumRepositoryInterface $forum): PodiumResponse
    {
        return $this->getMover()->move($id, $forum);
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
    public function pin(int $id): PodiumResponse
    {
        return $this->getPinner()->pin($id);
    }

    /**
     * Unpins thread.
     *
     * @throws InvalidConfigException
     */
    public function unpin(int $id): PodiumResponse
    {
        return $this->getPinner()->unpin($id);
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
    public function lock(int $id): PodiumResponse
    {
        return $this->getLocker()->lock($id);
    }

    /**
     * Unlocks thread.
     *
     * @throws InvalidConfigException
     */
    public function unlock(int $id): PodiumResponse
    {
        return $this->getLocker()->unlock($id);
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
    public function archive(int $id): PodiumResponse
    {
        return $this->getArchiver()->archive($id);
    }

    /**
     * Revives thread.
     *
     * @throws InvalidConfigException
     */
    public function revive(int $id): PodiumResponse
    {
        return $this->getArchiver()->revive($id);
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
    public function subscribe(MembershipInterface $member, ThreadRepositoryInterface $thread): PodiumResponse
    {
        return $this->getSubscriber()->subscribe($member, $thread);
    }

    /**
     * Unsubscribes from a thread.
     *
     * @throws InvalidConfigException
     */
    public function unsubscribe(MembershipInterface $member, ThreadRepositoryInterface $thread): PodiumResponse
    {
        return $this->getSubscriber()->unsubscribe($member, $thread);
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
    public function mark(MembershipInterface $member, PostRepositoryInterface $post): PodiumResponse
    {
        return $this->getBookmarker()->mark($member, $post);
    }
}
