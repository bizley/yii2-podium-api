<?php

declare(strict_types=1);

namespace bizley\podium\api\services\poll;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\VoteEvent;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\PollRepositoryInterface;
use bizley\podium\api\interfaces\VoterInterface;
use bizley\podium\api\repositories\PollRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

use function count;

final class PollVoter extends Component implements VoterInterface
{
    public const EVENT_BEFORE_VOTING = 'podium.poll.voting.before';
    public const EVENT_AFTER_VOTING = 'podium.poll.voting.after';

    private ?PollRepositoryInterface $poll = null;

    /**
     * @var string|array|PollRepositoryInterface
     */
    public $repositoryConfig = PollRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getPoll(): PollRepositoryInterface
    {
        if (null === $this->poll) {
            /** @var PollRepositoryInterface $poll */
            $poll = Instance::ensure($this->repositoryConfig, PollRepositoryInterface::class);
            $this->poll = $poll;
        }

        return $this->poll;
    }

    public function beforeVote(): bool
    {
        $event = new VoteEvent();
        $this->trigger(self::EVENT_BEFORE_VOTING, $event);

        return $event->canVote;
    }

    /**
     * Votes in the poll.
     */
    public function vote(
        MemberRepositoryInterface $member,
        PollRepositoryInterface $poll,
        array $answers
    ): PodiumResponse {
        $answersCount = count($answers);
        if (0 === $answersCount || !$this->beforeVote()) {
            return PodiumResponse::error();
        }

        try {
            $poll = $this->getPoll();
            if (!$poll->fetchOne($poll->getId())) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'poll.not.exists')]);
            }
            $memberId = $member->getId();
            if ($poll->hasMemberVoted($memberId)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'poll.already.voted')]);
            }
            if ($answersCount > 1 && $poll->isSingleChoice()) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'poll.one.vote.allowed')]);
            }
            if (!$poll->vote($memberId, $answers)) {
                return PodiumResponse::error($poll->getErrors());
            }

            $this->afterVote();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while deleting poll', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterVote(): void
    {
        $this->trigger(self::EVENT_AFTER_VOTING, new VoteEvent(['model' => $this]));
    }
}
