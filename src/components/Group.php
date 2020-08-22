<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\ars\GroupActiveRecord;
use bizley\podium\api\interfaces\BuilderInterface;
use bizley\podium\api\interfaces\GroupInterface;
use bizley\podium\api\interfaces\GroupRepositoryInterface;
use bizley\podium\api\interfaces\KeeperInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\repositories\GroupRepository;
use bizley\podium\api\services\group\GroupBuilder;
use bizley\podium\api\services\group\GroupKeeper;
use bizley\podium\api\services\group\GroupRemover;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use yii\di\Instance;

final class Group extends Component implements GroupInterface
{
    /**
     * @var string|array|BuilderInterface
     */
    public $builderConfig = GroupBuilder::class;

    /**
     * @var string|array|RemoverInterface
     */
    public $removerConfig = GroupRemover::class;

    /**
     * @var string|array|KeeperInterface
     */
    public $keeperConfig = GroupKeeper::class;

    /**
     * @var string|array|GroupRepositoryInterface
     */
    public $repositoryConfig = GroupRepository::class;

    /**
     * @throws InvalidConfigException
     */
    public function getById(int $id): ?GroupActiveRecord
    {
        /** @var GroupRepository $group */
        $group = Instance::ensure($this->repositoryConfig, GroupRepositoryInterface::class);
        if (!$group->fetchOne($id)) {
            return null;
        }

        return $group->getModel();
    }

    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    public function getAll(ActiveDataFilter $filter = null, $sort = null, $pagination = null): ActiveDataProvider
    {
        /** @var GroupRepository $group */
        $group = Instance::ensure($this->repositoryConfig, GroupRepositoryInterface::class);
        $group->fetchAll($filter, $sort, $pagination);

        return $group->getCollection();
    }

    /**
     * @throws InvalidConfigException
     */
    public function getBuilder(): BuilderInterface
    {
        /** @var BuilderInterface $builder */
        $builder = Instance::ensure($this->builderConfig, BuilderInterface::class);

        return $builder;
    }

    /**
     * Creates group.
     *
     * @throws InvalidConfigException
     */
    public function create(array $data): PodiumResponse
    {
        return $this->getBuilder()->create($data);
    }

    /**
     * Updates group.
     *
     * @throws InvalidConfigException
     */
    public function edit($id, array $data): PodiumResponse
    {
        return $this->getBuilder()->edit($id, $data);
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
     * Deletes group.
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
    public function getKeeper(): KeeperInterface
    {
        /** @var KeeperInterface $keeper */
        $keeper = Instance::ensure($this->keeperConfig, KeeperInterface::class);

        return $keeper;
    }

    /**
     * Adds member to the group.
     *
     * @throws InvalidConfigException
     */
    public function join($id, MemberRepositoryInterface $member): PodiumResponse
    {
        return $this->getKeeper()->join($id, $member);
    }

    /**
     * Removes member from a group.
     *
     * @throws InvalidConfigException
     */
    public function leave($id, MemberRepositoryInterface $member): PodiumResponse
    {
        return $this->getKeeper()->leave($id, $member);
    }
}
