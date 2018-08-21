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
     * @var string|array|PollModelInterface
     * Component ID, class, configuration array, or instance of PollModelInterface.
     */
    public $pollHandler = \bizley\podium\api\models\poll\Poll::class;

    /**
     * @var string|array|CategorisedFormInterface
     * Component ID, class, configuration array, or instance of CategorisedFormInterface.
     */
    public $pollFormHandler = \bizley\podium\api\models\poll\PostPollForm::class;

    /**
     * @var string|array|VotingInterface
     * Component ID, class, configuration array, or instance of VotingInterface.
     */
    public $votingHandler = \bizley\podium\api\models\poll\Voting::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->pollHandler = Instance::ensure($this->pollHandler, PollModelInterface::class);
        $this->pollFormHandler = Instance::ensure($this->pollFormHandler, CategorisedFormInterface::class);
        $this->votingHandler = Instance::ensure($this->votingHandler, VotingInterface::class);
    }

    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getPollByPostId(int $id): ?PollModelInterface
    {
        $pollClass = $this->pollHandler;
        return $pollClass::findById($id);
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
     * @return bool
     */
    public function create(array $data, MembershipInterface $author, ModelInterface $thread): bool
    {
        $pollForm = $this->getPollForm();
        $pollForm->setAuthor($author);
        $pollForm->setThread($thread);

        if (!$pollForm->loadData($data)) {
            return false;
        }
        return $pollForm->create();
    }

    /**
     * Updates poll post.
     * @param ModelFormInterface $postPollForm
     * @param array $data
     * @return bool
     */
    public function edit(ModelFormInterface $postPollForm, array $data): bool
    {
        if (!$postPollForm->loadData($data)) {
            return false;
        }
        return $postPollForm->edit();
    }

    /**
     * @param RemovableInterface $pollRemover
     * @return bool
     */
    public function remove(RemovableInterface $pollRemover): bool
    {
        return $pollRemover->remove();
    }

    /**
     * Returns poll form handler.
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
     * @return bool
     */
    public function vote(MembershipInterface $member, PollModelInterface $poll, array $answers): bool
    {
        $voting = $this->getVoting();
        $voting->setMember($member);
        $voting->setPoll($poll);
        $voting->setAnswers($answers);

        return $voting->vote();
    }
}
