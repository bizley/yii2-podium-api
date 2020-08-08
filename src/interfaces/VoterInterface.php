<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

/**
 * Interface VoterInterface
 * @package bizley\podium\api\interfaces
 */
interface VoterInterface
{
    /**
     * Initiator of voting.
     * @param MembershipInterface $member
     */
    public function setMember(MembershipInterface $member): void;

    /**
     * Poll to vote in.
     * @param PollModelInterface $poll
     */
    public function setPoll(PollModelInterface $poll): void;

    /**
     * Answers to vote for.
     * @param PollAnswerModelInterface[] $answers
     */
    public function setAnswers(array $answers): void;

    /**
     * @return PodiumResponse
     */
    public function vote(): PodiumResponse;
}
