<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\AccountInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\MessageParticipantModelInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\PollAnswerModelInterface;
use bizley\podium\api\interfaces\PollModelInterface;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\web\User;

/**
 * Class Account
 * @package bizley\podium\api\base
 *
 * @property null|MembershipInterface $membership
 * @property null|int $id
 */
class Account extends PodiumComponent implements AccountInterface
{
    /**
     * @var string|array|MembershipInterface membership handler
     * Component ID, class, configuration array, or instance of MembershipInterface.
     */
    public $membershipHandler = \bizley\podium\api\models\member\Member::class;

    /**
     * @var string|array|User user component handler
     * Component ID, class, configuration array, or instance of User.
     */
    public $userHandler = 'user';

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->membershipHandler = Instance::ensure($this->membershipHandler, MembershipInterface::class);
        $this->userHandler = Instance::ensure($this->userHandler, User::class);
    }

    private $_membership;

    /**
     * @return MembershipInterface|null
     */
    public function getMembership(): ?MembershipInterface
    {
        if ($this->_membership === null) {
            /* @var $class MembershipInterface */
            $class = $this->membershipHandler;
            $this->_membership = $class::findByUserId($this->userHandler->id);
        }
        return $this->_membership;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        $membership = $this->getMembership();
        if ($membership === null) {
            return null;
        }
        return $membership->getId() ?? null;
    }

    /**
     * @return MembershipInterface
     * @throws NoMembershipException
     */
    public function ensureMembership(): MembershipInterface
    {
        $member = $this->getMembership();
        if ($member === null) {
            throw new NoMembershipException('Membership data missing.');
        }
        return $member;
    }

    /**
     * Befriends target member.
     * @param MembershipInterface $target
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function befriend(MembershipInterface $target): PodiumResponse
    {
        return $this->podium->member->befriend($this->ensureMembership(), $target);
    }

    /**
     * Unfriends target member.
     * @param MembershipInterface $target
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function unfriend(MembershipInterface $target): PodiumResponse
    {
        return $this->podium->member->unfriend($this->ensureMembership(), $target);
    }

    /**
     * Ignores target member.
     * @param MembershipInterface $target
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function ignore(MembershipInterface $target): PodiumResponse
    {
        return $this->podium->member->ignore($this->ensureMembership(), $target);
    }

    /**
     * Unignores target member.
     * @param MembershipInterface $target
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function unignore(MembershipInterface $target): PodiumResponse
    {
        return $this->podium->member->unignore($this->ensureMembership(), $target);
    }

    /**
     * Gives post a thumb up.
     * @param ModelInterface $post
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function thumbUp(ModelInterface $post): PodiumResponse
    {
        return $this->podium->post->thumbUp($this->ensureMembership(), $post);
    }

    /**
     * Gives post a thumb down.
     * @param ModelInterface $post
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function thumbDown(ModelInterface $post): PodiumResponse
    {
        return $this->podium->post->thumbDown($this->ensureMembership(), $post);
    }

    /**
     * Resets post given thumb.
     * @param ModelInterface $post
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function thumbReset(ModelInterface $post): PodiumResponse
    {
        return $this->podium->post->thumbReset($this->ensureMembership(), $post);
    }

    /**
     * Subscribes to a thread.
     * @param ModelInterface $thread
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function subscribe(ModelInterface $thread): PodiumResponse
    {
        return $this->podium->thread->subscribe($this->ensureMembership(), $thread);
    }

    /**
     * Unsubscribes from a thread.
     * @param ModelInterface $thread
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function unsubscribe(ModelInterface $thread): PodiumResponse
    {
        return $this->podium->thread->unsubscribe($this->ensureMembership(), $thread);
    }

    /**
     * Marks last seen post in a thread.
     * @param ModelInterface $post
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function mark(ModelInterface $post): PodiumResponse
    {
        return $this->podium->thread->mark($this->ensureMembership(), $post);
    }

    /**
     * Adds member to a group.
     * @param ModelInterface $group
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function join(ModelInterface $group): PodiumResponse
    {
        return $this->podium->member->join($this->ensureMembership(), $group);
    }

    /**
     * Removes member from a group.
     * @param ModelInterface $group
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function leave(ModelInterface $group): PodiumResponse
    {
        return $this->podium->member->leave($this->ensureMembership(), $group);
    }

    /**
     * Votes in poll.
     * @param PollModelInterface $poll
     * @param PollAnswerModelInterface[] $answers
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function vote(PollModelInterface $poll, array $answers): PodiumResponse
    {
        return $this->podium->poll->vote($this->ensureMembership(), $poll, $answers);
    }

    /**
     * Sends message.
     * @param array $data
     * @param MembershipInterface $receiver
     * @param MessageParticipantModelInterface $replyTo
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function send(array $data, MembershipInterface $receiver, ?MessageParticipantModelInterface $replyTo = null): PodiumResponse
    {
        return $this->podium->message->send($data, $this->ensureMembership(), $receiver, $replyTo);
    }
}
