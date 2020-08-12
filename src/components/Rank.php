<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\ars\RankActiveRecord;
use bizley\podium\api\interfaces\ActiveRecordRankRepositoryInterface;
use bizley\podium\api\interfaces\BuilderInterface;
use bizley\podium\api\interfaces\RankInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\repositories\RankRepository;
use bizley\podium\api\services\rank\RankBuilder;
use bizley\podium\api\services\rank\RankRemover;
use yii\base\Component;
use yii\base\InvalidConfigException;
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
    public $removerHandler = RankRemover::class;

    /**
     * @var string|array|ActiveRecordRankRepositoryInterface
     */
    public $repositoryConfig = RankRepository::class;

    /**
     * @throws InvalidConfigException
     */
    public function getById(int $id): ?RankActiveRecord
    {
        /** @var ActiveRecordRankRepositoryInterface $thread */
        $thread = Instance::ensure($this->repositoryConfig, ActiveRecordRankRepositoryInterface::class);
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
        /** @var ActiveRecordRankRepositoryInterface $rank */
        $rank = Instance::ensure($this->repositoryConfig, ActiveRecordRankRepositoryInterface::class);
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
    public function create(array $data): PodiumResponse
    {
        return $this->getBuilder()->create($data);
    }

    /**
     * Updates rank.
     *
     * @throws InvalidConfigException
     */
    public function edit(int $id, array $data): PodiumResponse
    {
        return $this->getBuilder()->edit($id, $data);
    }

    public function getRemover(): RemoverInterface
    {
        /** @var RemoverInterface $remover */
        $remover = Instance::ensure($this->removerHandler, RemoverInterface::class);

        return $remover;
    }

    /**
     * Deletes rank.
     */
    public function remove(int $id): PodiumResponse
    {
        return $this->getRemover()->remove($id);
    }
}
