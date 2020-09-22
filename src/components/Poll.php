<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\interfaces\ActiveRecordRepositoryInterface;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\interfaces\PinnerInterface;
use bizley\podium\api\interfaces\PollBuilderInterface;
use bizley\podium\api\interfaces\PollInterface;
use bizley\podium\api\interfaces\PollRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\interfaces\VoterInterface;
use bizley\podium\api\repositories\PollRepository;
use bizley\podium\api\services\poll\PollArchiver;
use bizley\podium\api\services\poll\PollBuilder;
use bizley\podium\api\services\poll\PollMover;
use bizley\podium\api\services\poll\PollPinner;
use bizley\podium\api\services\poll\PollRemover;
use bizley\podium\api\services\poll\PollVoter;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\data\Sort;
use yii\db\ActiveRecord;
use yii\di\Instance;

final class Poll extends Component implements PollInterface
{
    /**
     * @var string|array|PollBuilderInterface
     */
    public $builderConfig = PollBuilder::class;

    /**
     * @var string|array|VoterInterface
     */
    public $voterConfig = PollVoter::class;

    /**
     * @var string|array|RemoverInterface
     */
    public $removerConfig = PollRemover::class;

    /**
     * @var string|array|MoverInterface
     */
    public $moverConfig = PollMover::class;

    /**
     * @var string|array|ArchiverInterface
     */
    public $archiverConfig = PollArchiver::class;

    /**
     * @var string|array|PinnerInterface
     */
    public $pinnerConfig = PollPinner::class;

    /**
     * @var string|array|PollRepositoryInterface
     */
    public $repositoryConfig = PollRepository::class;

    /**
     * @throws InvalidConfigException
     */
    public function getById(int $id): ?ActiveRecord
    {
        /** @var ActiveRecordRepositoryInterface $poll */
        $poll = Instance::ensure($this->repositoryConfig, ActiveRecordRepositoryInterface::class);
        if (!$poll->fetchOne($id)) {
            return null;
        }

        return $poll->getModel();
    }

    /**
     * @param bool|array|Sort|null       $sort
     * @param bool|array|Pagination|null $pagination
     *
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    public function getAll(ActiveDataFilter $filter = null, $sort = null, $pagination = null): ActiveDataProvider
    {
        /** @var PollRepository $poll */
        $poll = Instance::ensure($this->repositoryConfig, PollRepositoryInterface::class);
        $poll->fetchAll($filter, $sort, $pagination);

        return $poll->getCollection();
    }

    private ?PollBuilderInterface $builder = null;

    /**
     * @throws InvalidConfigException
     */
    public function getBuilder(): PollBuilderInterface
    {
        if (null === $this->builder) {
            /** @var PollBuilderInterface $builder */
            $builder = Instance::ensure($this->builderConfig, PollBuilderInterface::class);
            $this->builder = $builder;
        }

        return $this->builder;
    }

    /**
     * Creates poll.
     *
     * @throws InvalidConfigException
     */
    public function create(
        MemberRepositoryInterface $author,
        ThreadRepositoryInterface $thread,
        array $answers,
        array $data = []
    ): PodiumResponse {
        return $this->getBuilder()->create($author, $thread, $answers, $data);
    }

    /**
     * Updates poll.
     *
     * @throws InvalidConfigException
     */
    public function edit(PollRepositoryInterface $poll, array $answers, array $data = []): PodiumResponse
    {
        return $this->getBuilder()->edit($poll, $answers, $data);
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
     * Deletes poll.
     *
     * @throws InvalidConfigException
     */
    public function remove(PollRepositoryInterface $poll): PodiumResponse
    {
        return $this->getRemover()->remove($poll);
    }

    private ?VoterInterface $voter = null;

    /**
     * @throws InvalidConfigException
     */
    public function getVoter(): VoterInterface
    {
        if (null === $this->voter) {
            /** @var VoterInterface $voter */
            $voter = Instance::ensure($this->voterConfig, VoterInterface::class);
            $this->voter = $voter;
        }

        return $this->voter;
    }

    /**
     * Votes in poll.
     *
     * @throws InvalidConfigException
     */
    public function vote(
        PollRepositoryInterface $poll,
        MemberRepositoryInterface $member,
        array $answers
    ): PodiumResponse {
        return $this->getVoter()->vote($poll, $member, $answers);
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
     * Moves poll.
     *
     * @throws InvalidConfigException
     */
    public function move(PollRepositoryInterface $poll, ThreadRepositoryInterface $thread): PodiumResponse
    {
        return $this->getMover()->move($poll, $thread);
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
     * Archives poll.
     *
     * @throws InvalidConfigException
     */
    public function archive(PollRepositoryInterface $poll): PodiumResponse
    {
        return $this->getArchiver()->archive($poll);
    }

    /**
     * Revives poll.
     *
     * @throws InvalidConfigException
     */
    public function revive(PollRepositoryInterface $poll): PodiumResponse
    {
        return $this->getArchiver()->revive($poll);
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
     * Pins poll.
     *
     * @throws InvalidConfigException
     */
    public function pin(PollRepositoryInterface $poll): PodiumResponse
    {
        return $this->getPinner()->pin($poll);
    }

    /**
     * Unpins poll.
     *
     * @throws InvalidConfigException
     */
    public function unpin(PollRepositoryInterface $poll): PodiumResponse
    {
        return $this->getPinner()->unpin($poll);
    }
}
