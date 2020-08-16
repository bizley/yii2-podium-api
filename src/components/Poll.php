<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\ars\PollActiveRecord;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\MoverInterface;
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
use bizley\podium\api\services\poll\PollRemover;
use bizley\podium\api\services\poll\PollVoter;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
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
     * @var string|array|PollRepositoryInterface
     */
    public $repositoryConfig = PollRepository::class;

    /**
     * @throws InvalidConfigException
     */
    public function getById(int $id): ?PollActiveRecord
    {
        /** @var PollRepository $poll */
        $poll = Instance::ensure($this->repositoryConfig, PollRepositoryInterface::class);
        if (!$poll->fetchOne($id)) {
            return null;
        }

        return $poll->getModel();
    }

    /**
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

    /**
     * @throws InvalidConfigException
     */
    public function getBuilder(): PollBuilderInterface
    {
        /** @var PollBuilderInterface $builder */
        $builder = Instance::ensure($this->builderConfig, PollBuilderInterface::class);

        return $builder;
    }

    /**
     * Creates poll.
     *
     * @throws InvalidConfigException
     */
    public function create(
        array $data,
        array $answers,
        MemberRepositoryInterface $author,
        ThreadRepositoryInterface $thread
    ): PodiumResponse {
        return $this->getBuilder()->create($data, $answers, $author, $thread);
    }

    /**
     * Updates poll.
     *
     * @throws InvalidConfigException
     */
    public function edit($id, array $data, array $answers): PodiumResponse
    {
        return $this->getBuilder()->edit($id, $data, $answers);
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
     * Deletes poll.
     *
     * @throws InvalidConfigException
     */
    public function remove($id): PodiumResponse
    {
        return $this->getRemover()->remove($id);
    }

    /**
     * @throws InvalidConfigException
     */
    public function getVoter(): VoterInterface
    {
        /** @var VoterInterface $voter */
        $voter = Instance::ensure($this->voterConfig, VoterInterface::class);

        return $voter;
    }

    /**
     * Votes in poll.
     *
     * @throws InvalidConfigException
     */
    public function vote(
        MemberRepositoryInterface $member,
        PollRepositoryInterface $poll,
        array $answers
    ): PodiumResponse {
        return $this->getVoter()->vote($member, $poll, $answers);
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
     * Moves poll.
     *
     * @throws InvalidConfigException
     */
    public function move($id, ThreadRepositoryInterface $thread): PodiumResponse
    {
        return $this->getMover()->move($id, $thread);
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
     * Archives poll.
     *
     * @throws InvalidConfigException
     */
    public function archive($id): PodiumResponse
    {
        return $this->getArchiver()->archive($id);
    }

    /**
     * Revives poll.
     *
     * @throws InvalidConfigException
     */
    public function revive($id): PodiumResponse
    {
        return $this->getArchiver()->revive($id);
    }
}
