<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\ars\RankActiveRecord;
use bizley\podium\api\interfaces\BuilderInterface;
use bizley\podium\api\interfaces\RankInterface;
use bizley\podium\api\interfaces\RankRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\repositories\RankRepository;
use bizley\podium\api\services\rank\RankBuilder;
use bizley\podium\api\services\rank\RankRemover;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use yii\di\Instance;

final class Rank extends Component implements RankInterface
{
    /**
     * @var string|array|BuilderInterface
     */
    public $builderConfig = RankBuilder::class;

    /**
     * @var string|array|RemoverInterface
     */
    public $removerConfig = RankRemover::class;

    /**
     * @var string|array|RankRepositoryInterface
     */
    public $repositoryConfig = RankRepository::class;

    /**
     * @throws InvalidConfigException
     */
    public function getById($id): ?RankActiveRecord
    {
        /** @var RankRepository $rank */
        $rank = Instance::ensure($this->repositoryConfig, RankRepositoryInterface::class);
        if (!$rank->fetchOne($id)) {
            return null;
        }

        return $rank->getModel();
    }

    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    public function getAll(ActiveDataFilter $filter = null, $sort = null, $pagination = null): ActiveDataProvider
    {
        /** @var RankRepository $rank */
        $rank = Instance::ensure($this->repositoryConfig, RankRepositoryInterface::class);
        $rank->fetchAll($filter, $sort, $pagination);

        return $rank->getCollection();
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
     * Creates rank.
     *
     * @throws InvalidConfigException
     */
    public function create(array $data = []): PodiumResponse
    {
        return $this->getBuilder()->create($data);
    }

    /**
     * Updates rank.
     *
     * @throws InvalidConfigException
     */
    public function edit(RankRepositoryInterface $rank, array $data = []): PodiumResponse
    {
        return $this->getBuilder()->edit($rank, $data);
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
     * Deletes rank.
     *
     * @throws InvalidConfigException
     */
    public function remove(RankRepositoryInterface $rank): PodiumResponse
    {
        return $this->getRemover()->remove($rank);
    }
}
