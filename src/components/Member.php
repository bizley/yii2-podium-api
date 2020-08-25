<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\ars\MemberActiveRecord;
use bizley\podium\api\interfaces\AcquaintanceInterface;
use bizley\podium\api\interfaces\BanisherInterface;
use bizley\podium\api\interfaces\MemberBuilderInterface;
use bizley\podium\api\interfaces\MemberInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\repositories\MemberRepository;
use bizley\podium\api\services\member\MemberAcquaintance;
use bizley\podium\api\services\member\MemberBanisher;
use bizley\podium\api\services\member\MemberBuilder;
use bizley\podium\api\services\member\MemberRemover;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use yii\di\Instance;

final class Member extends Component implements MemberInterface
{
    /**
     * @var string|array|MemberBuilderInterface
     */
    public $builderConfig = MemberBuilder::class;

    /**
     * @var string|array|AcquaintanceInterface
     */
    public $acquaintanceConfig = MemberAcquaintance::class;

    /**
     * @var string|array|RemoverInterface
     */
    public $removerConfig = MemberRemover::class;

    /**
     * @var string|array|BanisherInterface
     */
    public $banisherConfig = MemberBanisher::class;

    /**
     * @var string|array|MemberRepositoryInterface
     */
    public $repositoryConfig = MemberRepository::class;

    /**
     * @throws InvalidConfigException
     */
    public function getById($id): ?MemberActiveRecord
    {
        /** @var MemberRepository $member */
        $member = Instance::ensure($this->repositoryConfig, MemberRepositoryInterface::class);
        if (!$member->fetchOne($id)) {
            return null;
        }

        return $member->getModel();
    }

    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    public function getAll(ActiveDataFilter $filter = null, $sort = null, $pagination = null): ActiveDataProvider
    {
        /** @var MemberRepository $member */
        $member = Instance::ensure($this->repositoryConfig, MemberRepositoryInterface::class);
        $member->fetchAll($filter, $sort, $pagination);

        return $member->getCollection();
    }

    /**
     * @throws InvalidConfigException
     */
    public function getBuilder(): MemberBuilderInterface
    {
        /** @var MemberBuilderInterface $builder */
        $builder = Instance::ensure($this->builderConfig, MemberBuilderInterface::class);

        return $builder;
    }

    /**
     * Registers member.
     *
     * @throws InvalidConfigException
     */
    public function register($id, array $data = []): PodiumResponse
    {
        return $this->getBuilder()->register($id, $data);
    }

    /**
     * Updates member.
     *
     * @throws InvalidConfigException
     */
    public function edit(MemberRepositoryInterface $member, array $data = []): PodiumResponse
    {
        return $this->getBuilder()->edit($member, $data);
    }

    /**
     * @throws InvalidConfigException
     */
    public function getAcquaintance(): AcquaintanceInterface
    {
        /** @var AcquaintanceInterface $acquaintance */
        $acquaintance = Instance::ensure($this->acquaintanceConfig, AcquaintanceInterface::class);

        return $acquaintance;
    }

    /**
     * Befriends the member.
     *
     * @throws InvalidConfigException
     */
    public function befriend(MemberRepositoryInterface $member, MemberRepositoryInterface $target): PodiumResponse
    {
        return $this->getAcquaintance()->befriend($member, $target);
    }

    /**
     * Unfriends the member.
     *
     * @throws InvalidConfigException
     */
    public function unfriend(MemberRepositoryInterface $member, MemberRepositoryInterface $target): PodiumResponse
    {
        return $this->getAcquaintance()->unfriend($member, $target);
    }

    /**
     * Ignores the member.
     *
     * @throws InvalidConfigException
     */
    public function ignore(MemberRepositoryInterface $member, MemberRepositoryInterface $target): PodiumResponse
    {
        return $this->getAcquaintance()->ignore($member, $target);
    }

    /**
     * Unignores the member.
     *
     * @throws InvalidConfigException
     */
    public function unignore(MemberRepositoryInterface $member, MemberRepositoryInterface $target): PodiumResponse
    {
        return $this->getAcquaintance()->unignore($member, $target);
    }

    /**
     * @throws InvalidConfigException
     */
    public function getBanisher(): BanisherInterface
    {
        /** @var BanisherInterface $banisher */
        $banisher = Instance::ensure($this->banisherConfig, BanisherInterface::class);

        return $banisher;
    }

    /**
     * Bans the member.
     *
     * @throws InvalidConfigException
     */
    public function ban(MemberRepositoryInterface $member): PodiumResponse
    {
        return $this->getBanisher()->ban($member);
    }

    /**
     * Unbans the member.
     *
     * @throws InvalidConfigException
     */
    public function unban(MemberRepositoryInterface $member): PodiumResponse
    {
        return $this->getBanisher()->unban($member);
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
     * Deletes member.
     *
     * @throws InvalidConfigException
     */
    public function remove(MemberRepositoryInterface $member): PodiumResponse
    {
        return $this->getRemover()->remove($member);
    }
}
