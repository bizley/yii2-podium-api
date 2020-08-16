<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\CategoryBuilderInterface;
use bizley\podium\api\interfaces\CategoryInterface;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\SorterInterface;
use bizley\podium\api\services\category\CategoryArchiver;
use bizley\podium\api\services\category\CategoryBuilder;
use bizley\podium\api\services\category\CategoryRemover;
use bizley\podium\api\services\category\CategorySorter;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class Category extends Component implements CategoryInterface
{
    /**
     * @var string|array|CategoryBuilderInterface
     */
    public $builderConfig = CategoryBuilder::class;

    /**
     * @var string|array|SorterInterface
     */
    public $sorterConfig = CategorySorter::class;

    /**
     * @var string|array|RemoverInterface
     */
    public $removerConfig = CategoryRemover::class;

    /**
     * @var string|array|ArchiverInterface
     */
    public $archiverConfig = CategoryArchiver::class;

    /**
     * @throws InvalidConfigException
     */
    public function getBuilder(): CategoryBuilderInterface
    {
        /** @var CategoryBuilderInterface $builder */
        $builder = Instance::ensure($this->builderConfig, CategoryBuilderInterface::class);

        return $builder;
    }

    /**
     * Creates category.
     *
     * @throws InvalidConfigException
     */
    public function create(array $data, MemberRepositoryInterface $author): PodiumResponse
    {
        return $this->getBuilder()->create($data, $author);
    }

    /**
     * Updates the category.
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
     * Deletes the category.
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
    public function getSorter(): SorterInterface
    {
        /** @var SorterInterface $sorter */
        $sorter = Instance::ensure($this->removerConfig, SorterInterface::class);

        return $sorter;
    }

    /**
     * Replaces the order of the categories.
     *
     * @throws InvalidConfigException
     */
    public function replace($id, CategoryRepositoryInterface $category): PodiumResponse
    {
        return $this->getSorter()->replace($id, $category);
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
     * Archives the category.
     *
     * @throws InvalidConfigException
     */
    public function archive($id): PodiumResponse
    {
        return $this->getArchiver()->archive($id);
    }

    /**
     * Revives the category.
     *
     * @throws InvalidConfigException
     */
    public function revive($id): PodiumResponse
    {
        return $this->getArchiver()->revive($id);
    }
}
