<?php

declare(strict_types=1);

namespace bizley\podium\api\models\poll;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\enums\PollChoice;
use bizley\podium\api\events\VoteEvent;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\PollAnswerModelInterface;
use bizley\podium\api\interfaces\PollModelInterface;
use bizley\podium\api\interfaces\VotingInterface;
use bizley\podium\api\repos\PollVoteRepo;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Model;
use yii\db\Exception;

/**
 * Class Voting
 * @package bizley\podium\api\models\poll
 */
class Voting extends Model implements VotingInterface
{
    public const EVENT_BEFORE_VOTING = 'podium.poll.voting.before';
    public const EVENT_AFTER_VOTING = 'podium.poll.voting.after';

    /**
     * @var int
     */
    public $member_id;

    /**
     * @var int
     */
    public $poll_id;

    /**
     * @var PollAnswerModelInterface[]
     */
    public $answers;

    /**
     * @var string
     */
    public $choice_id;

    /**
     * @param MembershipInterface $member
     */
    public function setMember(MembershipInterface $member): void
    {
        $this->member_id = $member->getId();
    }

    /**
     * @param PollModelInterface $poll
     */
    public function setPoll(PollModelInterface $poll): void
    {
        $this->poll_id = $poll->getId();
        $this->choice_id = $poll->getChoiceId();
    }

    /**
     * @param PollAnswerModelInterface[] $answers
     */
    public function setAnswers(array $answers): void
    {
        $this->answers = $answers;
    }

    /**
     * @return bool
     */
    public function beforeVote(): bool
    {
        $event = new VoteEvent();
        $this->trigger(self::EVENT_BEFORE_VOTING, $event);

        return $event->canVote;
    }

    /**
     * @return PodiumResponse
     */
    public function vote(): PodiumResponse
    {
        if (!$this->beforeVote()) {
            return PodiumResponse::error();
        }

        if (PollVoteRepo::find()->where([
                'member_id' => $this->member_id,
                'poll_id' => $this->poll_id,
            ])->exists()) {
            $this->addError('poll_id', Yii::t('podium.error', 'poll.already.voted'));
            return PodiumResponse::error($this);
        }

        if ($this->choice_id === PollChoice::SINGLE && \count($this->answers) > 1) {
            $this->addError('answers', Yii::t('podium.error', 'poll.one.vote.allowed'));
            return PodiumResponse::error($this);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            /* @var $pollAnswer PollAnswerModelInterface  */
            foreach ($this->answers as $pollAnswer) {

                if (!$pollAnswer instanceof PollAnswerModelInterface) {
                    throw new InvalidArgumentException('Poll Answer must be an instance of PollAnswerModelInterface!');
                }

                if ($pollAnswer->getPollId() !== $this->poll_id) {
                    throw new Exception('Wrong poll answer!');
                }

                $vote = new PollVoteForm([
                    'member_id' => $this->member_id,
                    'poll_id' => $this->poll_id,
                    'answer_id' => $pollAnswer->getId(),
                ]);
                if (!$vote->create()->result) {
                    throw new Exception('Error while voting!');
                }
            }

            $this->afterVote();

            $transaction->commit();

            return PodiumResponse::success();

        } catch (\Throwable $exc) {
            Yii::error(['Exception while voting', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            try {
                $transaction->rollBack();
            } catch (\Throwable $excTrans) {
                Yii::error(['Exception while voting transaction rollback', $excTrans->getMessage(), $excTrans->getTraceAsString()], 'podium');
            }
            return PodiumResponse::error();
        }
    }

    public function afterVote(): void
    {
        $this->trigger(self::EVENT_AFTER_VOTING, new VoteEvent(['model' => $this]));
    }
}
