<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\CategoryBuilderInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\PollInterface;
use bizley\podium\api\interfaces\PollModelInterface;
use bizley\podium\api\interfaces\PollRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\interfaces\VoterInterface;
use bizley\podium\api\repositories\PollRepository;
use bizley\podium\api\services\poll\PollRemover;
use bizley\podium\api\services\poll\PollVoter;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

final class Poll extends Component implements PollInterface
{
    /**
     * @var string|array|CategoryBuilderInterface
     */
    public $builderConfig = PollBuilder::class;

    /**
     * @var string|array|VoterInterface
     */
    public $voterConfig = PollVoter::class;

    /**
     * @var string|array|RemoverInterface
     */
    public $removerConfig = PollRemover::class;

    /**
     * @var string|array|PollRepositoryInterface
     */
    public $repositoryConfig = PollRepository::class;

    public function getById(int $id): ?PollModelInterface
    {
        $postClass = $this->modelHandler;

        return $postClass::findById($id);
    }

    public function getByThreadId(int $threadId): ?PollModelInterface
    {
        $pollClass = $this->modelHandler;

        return $pollClass::getByThreadId($threadId);
    }



    /**
     * Creates poll post.
     */
    public function create(
        array $data,
        MemberRepositoryInterface $author,
        ThreadRepositoryInterface $thread
    ): PodiumResponse {
        /* @var $pollForm CategorisedFormInterface */
        $pollForm = $this->getForm();

        $pollForm->setAuthor($author);
        $pollForm->setThread($thread);

        if (!$pollForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $pollForm->create();
    }

    /**
     * Updates poll post.
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

        $postPollForm = $this->getForm((int) $id);

        if (null === $postPollForm) {
            throw new ModelNotFoundException('Poll of given ID can not be found.');
        }

        if (!$postPollForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $postPollForm->edit();
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
     * Deletes poll.
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
    public function getVoter(): VoterInterface
    {
        /** @var VoterInterface $voter */
        $voter = Instance::ensure($this->voterConfig, VoterInterface::class);

        return $voter;
    }

    /**
     * Votes in poll.
     *
     * @throws InvalidConfigException
     */
    public function vote(
        MemberRepositoryInterface $member,
        PollRepositoryInterface $poll,
        array $answers
    ): PodiumResponse {
        return $this->getVoter()->vote($member, $poll, $answers);
    }
}
