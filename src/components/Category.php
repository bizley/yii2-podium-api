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
    public function create(MemberRepositoryInterface $author, array $data = []): PodiumResponse
    {
        return $this->getBuilder()->create($author, $data);
    }

    /**
     * Updates the category.
     *
     * @throws InvalidConfigException
     */
    public function edit(CategoryRepositoryInterface $category, array $data = []): PodiumResponse
    {
        return $this->getBuilder()->edit($category, $data);
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
    public function remove(CategoryRepositoryInterface $category): PodiumResponse
    {
        return $this->getRemover()->remove($category);
    }

    /**
     * @throws InvalidConfigException
     */
    public function getSorter(): SorterInterface
    {
        /** @var SorterInterface $sorter */
        $sorter = Instance::ensure($this->sorterConfig, SorterInterface::class);

        return $sorter;
    }

    /**
     * Replaces the order of the categories.
     *
     * @throws InvalidConfigException
     */
    public function replace(
        CategoryRepositoryInterface $firstCategory,
        CategoryRepositoryInterface $secondCategory
    ): PodiumResponse {
        return $this->getSorter()->replace($firstCategory, $secondCategory);
    }

    /**
     * @throws InvalidConfigException
     */
    public function sort(): PodiumResponse
    {
        return $this->getSorter()->sort();
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
    public function archive(CategoryRepositoryInterface $category): PodiumResponse
    {
        return $this->getArchiver()->archive($category);
    }

    /**
     * Revives the category.
     *
     * @throws InvalidConfigException
     */
    public function revive(CategoryRepositoryInterface $category): PodiumResponse
    {
        return $this->getArchiver()->revive($category);
    }
}
