<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\ArchiverInterface;
use bizley\podium\api\interfaces\CategorisedBuilderInterface;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\ForumInterface;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\MoverInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\SorterInterface;
use bizley\podium\api\models\forum\ForumMover;
use bizley\podium\api\repositories\ForumRepository;
use bizley\podium\api\services\forum\ForumArchiver;
use bizley\podium\api\services\forum\ForumRemover;
use bizley\podium\api\services\forum\ForumSorter;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

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
     * Creates forum.
     */
    public function create(
        array $data,
        MemberRepositoryInterface $author,
        CategoryRepositoryInterface $category
    ): PodiumResponse {
        /* @var $forumForm CategorisedFormInterface */
        $forumForm = $this->getForm();

        $forumForm->setAuthor($author);
        $forumForm->setCategory($category);

        if (!$forumForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $forumForm->create();
    }

    /**
     * Updates forum.
     *
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function edit(array $data): PodiumResponse
    {
        $id = ArrayHelper::remove($data, 'id');

        if (null === $id) {
            throw new InsufficientDataException('ID key is missing.');
        }

        $forumForm = $this->getForm((int) $id);

        if (null === $forumForm) {
            throw new ModelNotFoundException('Forum of given ID can not be found.');
        }

        if (!$forumForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $forumForm->edit();
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
        $sorter = Instance::ensure($this->sorterConfig, SorterInterface::class);

        return $sorter;
    }

    /**
     * Replaces the order of the forums.
     *
     * @throws InvalidConfigException
     */
    public function replace($id, ForumRepositoryInterface $forum): PodiumResponse
    {
        return $this->getSorter()->replace($id, $forum);
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
    public function move($id, CategoryRepositoryInterface $category): PodiumResponse
    {
        return $this->getMover()->move($id, $category);
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
    public function archive($id): PodiumResponse
    {
        return $this->getArchiver()->archive($id);
    }

    /**
     * Revives the forum.
     *
     * @throws InvalidConfigException
     */
    public function revive($id): PodiumResponse
    {
        return $this->getArchiver()->revive($id);
    }
}
