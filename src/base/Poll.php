<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\PollAnswerModelInterface;
use bizley\podium\api\interfaces\PollInterface;
use bizley\podium\api\interfaces\PollModelInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\VoterInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

/**
 * Class Poll
 * @package bizley\podium\api\base
 */
class Poll extends Component implements PollInterface
{
    /**
     * @var string|array|PollModelInterface poll handler
     * Component ID, class, configuration array, or instance of PollModelInterface.
     */
    public $modelHandler = \bizley\podium\api\models\poll\Poll::class;

    /**
     * @var string|array|CategorisedFormInterface poll form handler
     * Component ID, class, configuration array, or instance of CategorisedFormInterface.
     */
    public $formHandler = \bizley\podium\api\models\poll\PollForm::class;

    /**
     * @var string|array|VoterInterface voter handler
     * Component ID, class, configuration array, or instance of VoterInterface.
     */
    public $voterHandler = \bizley\podium\api\models\poll\PollVoter::class;

    /**
     * @var string|array|RemoverInterface poll remover handler
     * Component ID, class, configuration array, or instance of RemoverInterface.
     */
    public $removerHandler = \bizley\podium\api\models\poll\PollRemover::class;

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->modelHandler = Instance::ensure($this->modelHandler, PollModelInterface::class);
        $this->formHandler = Instance::ensure($this->formHandler, CategorisedFormInterface::class);
        $this->voterHandler = Instance::ensure($this->voterHandler, VoterInterface::class);
        $this->removerHandler = Instance::ensure($this->removerHandler, RemoverInterface::class);
    }

    /**
     * @param int $id
     * @return PollModelInterface|null
     */
    public function getById(int $id): ?PollModelInterface
    {
        $postClass = $this->modelHandler;

        return $postClass::findById($id);
    }

    /**
     * @param int $threadId
     * @return PollModelInterface|null
     */
    public function getByThreadId(int $threadId): ?PollModelInterface
    {
        $pollClass = $this->modelHandler;

        return $pollClass::getByThreadId($threadId);
    }

    /**
     * @param int|null $id
     * @return CategorisedFormInterface|null
     */
    public function getForm(int $id = null): ?CategorisedFormInterface
    {
        $handler = $this->formHandler;

        if ($id === null) {
            return new $handler;
        }

        return $handler::findById($id);
    }

    /**
     * Creates poll post.
     * @param array $data
     * @param MembershipInterface $author
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function create(array $data, MembershipInterface $author, ModelInterface $thread): PodiumResponse
    {
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
     * @param array $data
     * @return PodiumResponse
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function edit(array $data): PodiumResponse
    {
        $id = ArrayHelper::remove($data, 'id');

        if ($id === null) {
            throw new InsufficientDataException('ID key is missing.');
        }

        $postPollForm = $this->getForm((int)$id);

        if ($postPollForm === null) {
            throw new ModelNotFoundException('Poll of given ID can not be found.');
        }

        if (!$postPollForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $postPollForm->edit();
    }

    /**
     * @param int $id
     * @return RemoverInterface|null
     */
    public function getRemover(int $id): ?RemoverInterface
    {
        $handler = $this->removerHandler;

        return $handler::findById($id);
    }

    /**
     * Deletes poll.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function remove(int $id): PodiumResponse
    {
        $pollRemover = $this->getRemover($id);

        if ($pollRemover === null) {
            throw new ModelNotFoundException('Poll of given ID can not be found.');
        }

        return $pollRemover->remove();
    }

    /**
     * @return VoterInterface
     */
    public function getVoter(): VoterInterface
    {
        return new $this->voterHandler;
    }

    /**
     * Votes in poll.
     * @param MembershipInterface $member
     * @param PollModelInterface $poll
     * @param PollAnswerModelInterface[] $answers
     * @return PodiumResponse
     */
    public function vote(MembershipInterface $member, PollModelInterface $poll, array $answers): PodiumResponse
    {
        $voter = $this->getVoter();

        $voter->setMember($member);
        $voter->setPoll($poll);
        $voter->setAnswers($answers);

        return $voter->vote();
    }
}
