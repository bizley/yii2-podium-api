<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\ars\ForumActiveRecord;
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
    public function getById(int $id): ?ForumActiveRecord
    {
        /** @var ForumRepository $forum */
        $forum = Instance::ensure($this->repositoryConfig, ForumRepositoryInterface::class);
        if (!$forum->fetchOne($id)) {
            return null;
        }

        return $forum->getModel();
    }

    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    public function getAll(ActiveDataFilter $filter = null, $sort = null, $pagination = null): ActiveDataProvider
    {
        /** @var ForumRepository $thread */
        $thread = Instance::ensure($this->repositoryConfig, ForumRepositoryInterface::class);
        $thread->fetchAll($filter, $sort, $pagination);

        return $thread->getCollection();
    }

    /**
     * @throws InvalidConfigException
     */
    public function getBuilder(): CategorisedBuilderInterface
    {
        /** @var CategorisedBuilderInterface $builder */
        $builder = Instance::ensure($this->builderConfig, CategorisedBuilderInterface::class);

        return $builder;
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
     * Deletes the forum.
     *
     * @throws InvalidConfigException
     */
    public function remove(ForumRepositoryInterface $forum): PodiumResponse
    {
        return $this->getRemover()->remove($forum);
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

    /**
     * @throws InvalidConfigException
     */
    public function getMover(): MoverInterface
    {
        /** @var MoverInterface $mover */
        $mover = Instance::ensure($this->moverConfig, MoverInterface::class);

        return $mover;
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
