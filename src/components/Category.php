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
use yii\data\Pagination;
use yii\data\Sort;
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
    public function getById(int $id): ?CategoryActiveRecord
    {
        /** @var CategoryRepository $category */
        $category = Instance::ensure($this->repositoryConfig, CategoryRepositoryInterface::class);
        if (!$category->fetchOne($id)) {
            return null;
        }

        return $category->getModel();
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
            /** @var CategoryBuilderInterface $builder */
            $builder = Instance::ensure($this->builderConfig, CategoryBuilderInterface::class);
            $this->builder = $builder;
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
            /** @var RemoverInterface $remover */
            $remover = Instance::ensure($this->removerConfig, RemoverInterface::class);
            $this->remover = $remover;
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
            /** @var SorterInterface $sorter */
            $sorter = Instance::ensure($this->sorterConfig, SorterInterface::class);
            $this->sorter = $sorter;
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
            /** @var ArchiverInterface $archiver */
            $archiver = Instance::ensure($this->archiverConfig, ArchiverInterface::class);
            $this->archiver = $archiver;
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
