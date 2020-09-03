<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\ars\CategoryActiveRecord;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\CategoryBuilderInterface;
use bizley\podium\api\interfaces\CategoryInterface;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\SorterInterface;
use bizley\podium\api\repositories\CategoryRepository;
use bizley\podium\api\services\category\CategoryArchiver;
use bizley\podium\api\services\category\CategoryBuilder;
use bizley\podium\api\services\category\CategoryRemover;
use bizley\podium\api\services\category\CategorySorter;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
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
     * @var string|array|CategoryRepositoryInterface
     */
    public $repositoryConfig = CategoryRepository::class;

    /**
     * @throws InvalidConfigException
     */
    public function getById($id): ?CategoryActiveRecord
    {
        /** @var CategoryRepository $category */
        $category = Instance::ensure($this->repositoryConfig, CategoryRepositoryInterface::class);
        if (!$category->fetchOne($id)) {
            return null;
        }

        return $category->getModel();
    }

    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    public function getAll(ActiveDataFilter $filter = null, $sort = null, $pagination = null): ActiveDataProvider
    {
        /** @var CategoryRepository $thread */
        $thread = Instance::ensure($this->repositoryConfig, CategoryRepositoryInterface::class);
        $thread->fetchAll($filter, $sort, $pagination);

        return $thread->getCollection();
    }

    private ?CategoryBuilderInterface $builder = null;

    /**
     * @throws InvalidConfigException
     */
    public function getBuilder(): CategoryBuilderInterface
    {
        if (null === $this->builder) {
            $this->builder = Instance::ensure($this->builderConfig, CategoryBuilderInterface::class);
        }

        return $this->builder;
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

    private ?RemoverInterface $remover = null;

    /**
     * @throws InvalidConfigException
     */
    public function getRemover(): RemoverInterface
    {
        if (null === $this->remover) {
            $this->remover = Instance::ensure($this->removerConfig, RemoverInterface::class);
        }

        return $this->remover;
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

    private ?SorterInterface $sorter = null;

    /**
     * @throws InvalidConfigException
     */
    public function getSorter(): SorterInterface
    {
        if (null === $this->sorter) {
            $this->sorter = Instance::ensure($this->sorterConfig, SorterInterface::class);
        }

        return $this->sorter;
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

    private ?ArchiverInterface $archiver = null;

    /**
     * @throws InvalidConfigException
     */
    public function getArchiver(): ArchiverInterface
    {
        if (null === $this->archiver) {
            $this->archiver = Instance::ensure($this->archiverConfig, ArchiverInterface::class);
        }

        return $this->archiver;
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
