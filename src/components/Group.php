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
use yii\data\Pagination;
use yii\data\Sort;
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
     * @param bool|array|Sort|null       $sort
     * @param bool|array|Pagination|null $pagination
     *
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

    private ?BuilderInterface $builder = null;

    /**
     * @throws InvalidConfigException
     */
    public function getBuilder(): BuilderInterface
    {
        if (null === $this->builder) {
            /** @var BuilderInterface $builder */
            $builder = Instance::ensure($this->builderConfig, BuilderInterface::class);
            $this->builder = $builder;
        }

        return $this->builder;
    }

    /**
     * Creates group.
     *
     * @throws InvalidConfigException
     */
    public function create(array $data = []): PodiumResponse
    {
        return $this->getBuilder()->create($data);
    }

    /**
     * Updates group.
     *
     * @throws InvalidConfigException
     */
    public function edit(GroupRepositoryInterface $group, array $data = []): PodiumResponse
    {
        return $this->getBuilder()->edit($group, $data);
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
     * Deletes group.
     *
     * @throws InvalidConfigException
     */
    public function remove(GroupRepositoryInterface $group): PodiumResponse
    {
        return $this->getRemover()->remove($group);
    }

    private ?KeeperInterface $keeper = null;

    /**
     * @throws InvalidConfigException
     */
    public function getKeeper(): KeeperInterface
    {
        if (null === $this->keeper) {
            /** @var KeeperInterface $keeper */
            $keeper = Instance::ensure($this->keeperConfig, KeeperInterface::class);
            $this->keeper = $keeper;
        }

        return $this->keeper;
    }

    /**
     * Adds member to the group.
     *
     * @throws InvalidConfigException
     */
    public function join(GroupRepositoryInterface $group, MemberRepositoryInterface $member): PodiumResponse
    {
        return $this->getKeeper()->join($group, $member);
    }

    /**
     * Removes member from a group.
     *
     * @throws InvalidConfigException
     */
    public function leave(GroupRepositoryInterface $group, MemberRepositoryInterface $member): PodiumResponse
    {
        return $this->getKeeper()->leave($group, $member);
    }
}
