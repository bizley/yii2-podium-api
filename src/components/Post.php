<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\ars\PostActiveRecord;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\CategorisedBuilderInterface;
use bizley\podium\api\interfaces\LikerInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\interfaces\PostInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\repositories\PostRepository;
use bizley\podium\api\services\post\PostArchiver;
use bizley\podium\api\services\post\PostBuilder;
use bizley\podium\api\services\post\PostLiker;
use bizley\podium\api\services\post\PostMover;
use bizley\podium\api\services\post\PostRemover;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\data\Sort;
use yii\di\Instance;

final class Post extends Component implements PostInterface
{
    /**
     * @var string|array|CategorisedBuilderInterface
     */
    public $builderConfig = PostBuilder::class;

    /**
     * @var string|array|LikerInterface
     */
    public $likerConfig = PostLiker::class;

    /**
     * @var string|array|RemoverInterface
     */
    public $removerConfig = PostRemover::class;

    /**
     * @var string|array|ArchiverInterface
     */
    public $archiverConfig = PostArchiver::class;

    /**
     * @var string|array|MoverInterface
     */
    public $moverConfig = PostMover::class;

    /**
     * @var string|array|PostRepositoryInterface
     */
    public $repositoryConfig = PostRepository::class;

    /**
     * @throws InvalidConfigException
     */
    public function getById(int $id): ?PostActiveRecord
    {
        /** @var PostRepository $post */
        $post = Instance::ensure($this->repositoryConfig, PostRepositoryInterface::class);
        if (!$post->fetchOne($id)) {
            return null;
        }

        return $post->getModel();
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
        /** @var PostRepository $poll */
        $poll = Instance::ensure($this->repositoryConfig, PostRepositoryInterface::class);
        $poll->fetchAll($filter, $sort, $pagination);

        return $poll->getCollection();
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
     * Creates post.
     *
     * @throws InvalidConfigException
     */
    public function create(
        MemberRepositoryInterface $author,
        ThreadRepositoryInterface $thread,
        array $data = []
    ): PodiumResponse {
        return $this->getBuilder()->create($author, $thread, $data);
    }

    /**
     * Updates post.
     *
     * @throws InvalidConfigException
     */
    public function edit(PostRepositoryInterface $post, array $data = []): PodiumResponse
    {
        return $this->getBuilder()->edit($post, $data);
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
     * Deletes post.
     *
     * @throws InvalidConfigException
     */
    public function remove(PostRepositoryInterface $post): PodiumResponse
    {
        return $this->getRemover()->remove($post);
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
     * Moves post.
     *
     * @throws InvalidConfigException
     */
    public function move(PostRepositoryInterface $post, ThreadRepositoryInterface $thread): PodiumResponse
    {
        return $this->getMover()->move($post, $thread);
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
     * Archives post.
     *
     * @throws InvalidConfigException
     */
    public function archive(PostRepositoryInterface $post): PodiumResponse
    {
        return $this->getArchiver()->archive($post);
    }

    /**
     * Revives post.
     *
     * @throws InvalidConfigException
     */
    public function revive(PostRepositoryInterface $post): PodiumResponse
    {
        return $this->getArchiver()->revive($post);
    }

    private ?LikerInterface $liker = null;

    /**
     * @throws InvalidConfigException
     */
    public function getLiker(): LikerInterface
    {
        if (null === $this->liker) {
            /** @var LikerInterface $liker */
            $liker = Instance::ensure($this->likerConfig, LikerInterface::class);
            $this->liker = $liker;
        }

        return $this->liker;
    }

    /**
     * Gives post a thumb up.
     *
     * @throws InvalidConfigException
     */
    public function thumbUp(PostRepositoryInterface $post, MemberRepositoryInterface $member): PodiumResponse
    {
        return $this->getLiker()->thumbUp($post, $member);
    }

    /**
     * Gives post a thumb down.
     *
     * @throws InvalidConfigException
     */
    public function thumbDown(PostRepositoryInterface $post, MemberRepositoryInterface $member): PodiumResponse
    {
        return $this->getLiker()->thumbDown($post, $member);
    }

    /**
     * Resets post given thumb.
     *
     * @throws InvalidConfigException
     */
    public function thumbReset(PostRepositoryInterface $post, MemberRepositoryInterface $member): PodiumResponse
    {
        return $this->getLiker()->thumbReset($post, $member);
    }
}
