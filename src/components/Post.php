<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\ars\PostActiveRecord;
use bizley\podium\api\interfaces\ActiveRecordPostRepositoryInterface;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\CategoryBuilderInterface;
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
use yii\data\ActiveDataProvider;
use yii\di\Instance;

final class Post extends Component implements PostInterface
{
    /**
     * @var string|array|CategoryBuilderInterface
     */
    public $builderConfig = PostBuilder::class;

    /**
     * @var string|array|LikerInterface
     */
    public $likerHandler = PostLiker::class;

    /**
     * @var string|array|RemoverInterface
     */
    public $removerHandler = PostRemover::class;

    /**
     * @var string|array|ArchiverInterface
     */
    public $archiverHandler = PostArchiver::class;

    /**
     * @var string|array|MoverInterface
     */
    public $moverHandler = PostMover::class;

    /**
     * @var string|array|ActiveRecordPostRepositoryInterface
     */
    public $repositoryConfig = PostRepository::class;

    /**
     * @throws InvalidConfigException
     */
    public function getById(int $id): ?PostActiveRecord
    {
        /** @var ActiveRecordPostRepositoryInterface $post */
        $post = Instance::ensure($this->repositoryConfig, ActiveRecordPostRepositoryInterface::class);
        if (!$post->fetchOne($id)) {
            return null;
        }

        return $post->getModel();
    }

    /**
     * @throws InvalidConfigException
     */
    public function getAll($filter = null, $sort = null, $pagination = null): ActiveDataProvider
    {
        /** @var ActiveRecordPostRepositoryInterface $rank */
        $rank = Instance::ensure($this->repositoryConfig, ActiveRecordPostRepositoryInterface::class);
        $rank->fetchAll($filter, $sort, $pagination);

        return $rank->getCollection();
    }

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
     * Creates post.
     *
     * @throws InvalidConfigException
     */
    public function create(
        array $data,
        MemberRepositoryInterface $author,
        ThreadRepositoryInterface $thread
    ): PodiumResponse {
        return $this->getBuilder()->create($data, $author, $thread);
    }

    /**
     * Updates post.
     *
     * @throws InvalidConfigException
     */
    public function edit(int $id, array $data): PodiumResponse
    {
        return $this->getBuilder()->edit($id, $data);
    }

    /**
     * @throws InvalidConfigException
     */
    public function getRemover(): RemoverInterface
    {
        /** @var RemoverInterface $remover */
        $remover = Instance::ensure($this->removerHandler, RemoverInterface::class);

        return $remover;
    }

    /**
     * Deletes post.
     *
     * @throws InvalidConfigException
     */
    public function remove(int $id): PodiumResponse
    {
        return $this->getRemover()->remove($id);
    }

    /**
     * @throws InvalidConfigException
     */
    public function getMover(): MoverInterface
    {
        /** @var MoverInterface $mover */
        $mover = Instance::ensure($this->moverHandler, MoverInterface::class);

        return $mover;
    }

    /**
     * Moves post.
     *
     * @throws InvalidConfigException
     */
    public function move(int $id, ThreadRepositoryInterface $thread): PodiumResponse
    {
        return $this->getMover()->move($id, $thread);
    }

    /**
     * @throws InvalidConfigException
     */
    public function getArchiver(): ArchiverInterface
    {
        /** @var ArchiverInterface $archiver */
        $archiver = Instance::ensure($this->archiverHandler, ArchiverInterface::class);

        return $archiver;
    }

    /**
     * Archives post.
     *
     * @throws InvalidConfigException
     */
    public function archive(int $id): PodiumResponse
    {
        return $this->getArchiver()->archive($id);
    }

    /**
     * Revives post.
     *
     * @throws InvalidConfigException
     */
    public function revive(int $id): PodiumResponse
    {
        return $this->getArchiver()->revive($id);
    }

    /**
     * @throws InvalidConfigException
     */
    public function getLiker(): LikerInterface
    {
        /** @var LikerInterface $liker */
        $liker = Instance::ensure($this->likerHandler, LikerInterface::class);

        return $liker;
    }

    /**
     * Gives post a thumb up.
     *
     * @throws InvalidConfigException
     */
    public function thumbUp(MemberRepositoryInterface $member, PostRepositoryInterface $post): PodiumResponse
    {
        return $this->getLiker()->thumbUp($member, $post);
    }

    /**
     * Gives post a thumb down.
     *
     * @throws InvalidConfigException
     */
    public function thumbDown(MemberRepositoryInterface $member, PostRepositoryInterface $post): PodiumResponse
    {
        return $this->getLiker()->thumbDown($member, $post);
    }

    /**
     * Resets post given thumb.
     *
     * @throws InvalidConfigException
     */
    public function thumbReset(MemberRepositoryInterface $member, PostRepositoryInterface $post): PodiumResponse
    {
        return $this->getLiker()->thumbReset($member, $post);
    }
}
