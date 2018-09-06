<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\CategorisedFormInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\PollAnswerModelInterface;
use bizley\podium\api\interfaces\PollInterface;
use bizley\podium\api\interfaces\PollModelInterface;
use bizley\podium\api\interfaces\RemovableInterface;
use bizley\podium\api\interfaces\VotingInterface;
use yii\di\Instance;

/**
 * Class Poll
 * @package bizley\podium\api\base
 */
class Poll extends PodiumComponent implements PollInterface
{
    /**
     * @var string|array|PollModelInterface poll handler
     * Component ID, class, configuration array, or instance of PollModelInterface.
     */
    public $pollHandler = \bizley\podium\api\models\poll\Poll::class;

    /**
     * @var string|array|CategorisedFormInterface poll form handler
     * Component ID, class, configuration array, or instance of CategorisedFormInterface.
     */
    public $pollFormHandler = \bizley\podium\api\models\poll\PostPollForm::class;

    /**
     * @var string|array|VotingInterface voting handler
     * Component ID, class, configuration array, or instance of VotingInterface.
     */
    public $votingHandler = \bizley\podium\api\models\poll\Voting::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->pollHandler = Instance::ensure($this->pollHandler, PollModelInterface::class);
        $this->pollFormHandler = Instance::ensure($this->pollFormHandler, CategorisedFormInterface::class);
        $this->votingHandler = Instance::ensure($this->votingHandler, VotingInterface::class);
    }

    /**
     * @param int $id
     * @return PollModelInterface|null
     */
    public function getPollByPostId(int $id): ?PollModelInterface
    {
        $pollClass = $this->pollHandler;
        return $pollClass::findByPostId($id);
    }

    /**
     * Returns poll form handler.
     * @return CategorisedFormInterface
     */
    public function getPollForm(): CategorisedFormInterface
    {
        return new $this->pollFormHandler;
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
        $pollForm = $this->getPollForm();
        $pollForm->setAuthor($author);
        $pollForm->setThread($thread);

        if (!$pollForm->loadData($data)) {
            return PodiumResponse::error();
        }
        return $pollForm->create();
    }

    /**
     * Updates poll post.
     * @param ModelFormInterface $postPollForm
     * @param array $data
     * @return PodiumResponse
     */
    public function edit(ModelFormInterface $postPollForm, array $data): PodiumResponse
    {
        if (!$postPollForm->loadData($data)) {
            return PodiumResponse::error();
        }
        return $postPollForm->edit();
    }

    /**
     * Deletes poll.
     * @param RemovableInterface $pollRemover
     * @return PodiumResponse
     */
    public function remove(RemovableInterface $pollRemover): PodiumResponse
    {
        return $pollRemover->remove();
    }

    /**
     * @return VotingInterface
     */
    public function getVoting(): VotingInterface
    {
        return new $this->votingHandler;
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
        $voting = $this->getVoting();
        $voting->setMember($member);
        $voting->setPoll($poll);
        $voting->setAnswers($answers);

        return $voting->vote();
    }
}
