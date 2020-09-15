<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\interfaces\ActiveRecordRepositoryInterface;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\CategorisedBuilderInterface;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\ForumInterface;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\SorterInterface;
use bizley\podium\api\repositories\ForumRepository;
use bizley\podium\api\services\forum\ForumArchiver;
use bizley\podium\api\services\forum\ForumBuilder;
use bizley\podium\api\services\forum\ForumMover;
use bizley\podium\api\services\forum\ForumRemover;
use bizley\podium\api\services\forum\ForumSorter;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\data\Sort;
use yii\db\ActiveRecord;
use yii\di\Instance;

final class Forum extends Component implements ForumInterface
{
    /**
     * @var string|array|CategorisedBuilderInterface
     */
    public $builderConfig = ForumBuilder::class;

    /**
     * @var string|array|SorterInterface
     */
    public $sorterConfig = ForumSorter::class;

    /**
     * @var string|array|RemoverInterface
     */
    public $removerConfig = ForumRemover::class;

    /**
     * @var string|array|ArchiverInterface
     */
    public $archiverConfig = ForumArchiver::class;

    /**
     * @var string|array|MoverInterface
     */
    public $moverConfig = ForumMover::class;

    /**
     * @var string|array|ForumRepositoryInterface
     */
    public $repositoryConfig = ForumRepository::class;

    /**
     * @throws InvalidConfigException
     */
    public function getById(int $id): ?ActiveRecord
    {
        /** @var ActiveRecordRepositoryInterface $forum */
        $forum = Instance::ensure($this->repositoryConfig, ActiveRecordRepositoryInterface::class);
        if (!$forum->fetchOne($id)) {
            return null;
        }

        return $forum->getModel();
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
        /** @var ActiveRecordRepositoryInterface $thread */
        $thread = Instance::ensure($this->repositoryConfig, ActiveRecordRepositoryInterface::class);
        $thread->fetchAll($filter, $sort, $pagination);

        return $thread->getCollection();
    }

    private ?CategorisedBuilderInterface $builder = null;

    /**
     * @throws InvalidConfigException
     */
    public function getBuilder(): CategorisedBuilderInterface
    {
        if (null === $this->builder) {
            /** @var CategorisedBuilderInterface $builder */
            $builder = Instance::ensure($this->builderConfig, CategorisedBuilderInterface::class);
            $this->builder = $builder;
        }

        return $this->builder;
    }

    /**
     * Creates thread.
     *
     * @throws InvalidConfigException
     */
    public function create(
        MemberRepositoryInterface $author,
        CategoryRepositoryInterface $category,
        array $data = []
    ): PodiumResponse {
        return $this->getBuilder()->create($author, $category, $data);
    }

    /**
     * Updates thread.
     *
     * @throws InvalidConfigException
     */
    public function edit(ForumRepositoryInterface $forum, array $data = []): PodiumResponse
    {
        return $this->getBuilder()->edit($forum, $data);
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
     * Deletes the forum.
     *
     * @throws InvalidConfigException
     */
    public function remove(ForumRepositoryInterface $forum): PodiumResponse
    {
        return $this->getRemover()->remove($forum);
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
     * Replaces the order of the forums.
     *
     * @throws InvalidConfigException
     */
    public function replace(
        ForumRepositoryInterface $firstForum,
        ForumRepositoryInterface $secondForum
    ): PodiumResponse {
        return $this->getSorter()->replace($firstForum, $secondForum);
    }

    /**
     * Sorts the forums.
     *
     * @throws InvalidConfigException
     */
    public function sort(): PodiumResponse
    {
        return $this->getSorter()->sort();
    }

    private ?MoverInterface $mover = null;

    /**
     * @throws InvalidConfigException
     */
    public function getMover(): MoverInterface
    {
        if (null === $this->mover) {
            /** @var MoverInterface $mover */
            $mover = Instance::ensure($this->moverConfig, MoverInterface::class);
            $this->mover = $mover;
        }

        return $this->mover;
    }

    /**
     * Moves the forum.
     *
     * @throws InvalidConfigException
     */
    public function move(ForumRepositoryInterface $forum, CategoryRepositoryInterface $category): PodiumResponse
    {
        return $this->getMover()->move($forum, $category);
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
     * Archives the forum.
     *
     * @throws InvalidConfigException
     */
    public function archive(ForumRepositoryInterface $forum): PodiumResponse
    {
        return $this->getArchiver()->archive($forum);
    }

    /**
     * Revives the forum.
     *
     * @throws InvalidConfigException
     */
    public function revive(ForumRepositoryInterface $forum): PodiumResponse
    {
        return $this->getArchiver()->revive($forum);
    }
}
