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
 * @property MembershipInterface|null $membership
 * @property int|null $id
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

        return $membership->getId();
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
    public function befriendMember(MembershipInterface $target): PodiumResponse
    {
        return $this->podium->member->befriend($this->ensureMembership(), $target);
    }

    /**
     * Unfriends target member.
     * @param MembershipInterface $target
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function unfriendMember(MembershipInterface $target): PodiumResponse
    {
        return $this->podium->member->unfriend($this->ensureMembership(), $target);
    }

    /**
     * Ignores target member.
     * @param MembershipInterface $target
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function ignoreMember(MembershipInterface $target): PodiumResponse
    {
        return $this->podium->member->ignore($this->ensureMembership(), $target);
    }

    /**
     * Unignores target member.
     * @param MembershipInterface $target
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function unignoreMember(MembershipInterface $target): PodiumResponse
    {
        return $this->podium->member->unignore($this->ensureMembership(), $target);
    }

    /**
     * Gives post a thumb up.
     * @param ModelInterface $post
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function thumbUpPost(ModelInterface $post): PodiumResponse
    {
        return $this->podium->post->thumbUp($this->ensureMembership(), $post);
    }

    /**
     * Gives post a thumb down.
     * @param ModelInterface $post
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function thumbDownPost(ModelInterface $post): PodiumResponse
    {
        return $this->podium->post->thumbDown($this->ensureMembership(), $post);
    }

    /**
     * Resets post given thumb.
     * @param ModelInterface $post
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function thumbResetPost(ModelInterface $post): PodiumResponse
    {
        return $this->podium->post->thumbReset($this->ensureMembership(), $post);
    }

    /**
     * Marks last seen post in a thread.
     * @param ModelInterface $post
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function markPost(ModelInterface $post): PodiumResponse
    {
        return $this->podium->thread->mark($this->ensureMembership(), $post);
    }

    /**
     * Subscribes to a thread.
     * @param ModelInterface $thread
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function subscribeThread(ModelInterface $thread): PodiumResponse
    {
        return $this->podium->thread->subscribe($this->ensureMembership(), $thread);
    }

    /**
     * Unsubscribes from a thread.
     * @param ModelInterface $thread
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function unsubscribeThread(ModelInterface $thread): PodiumResponse
    {
        return $this->podium->thread->unsubscribe($this->ensureMembership(), $thread);
    }

    /**
     * Adds member to a group.
     * @param ModelInterface $group
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function joinGroup(ModelInterface $group): PodiumResponse
    {
        return $this->podium->member->join($this->ensureMembership(), $group);
    }

    /**
     * Removes member from a group.
     * @param ModelInterface $group
     * @return PodiumResponse
     * @throws NoMembershipException
     */
    public function leaveGroup(ModelInterface $group): PodiumResponse
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
    public function votePoll(PollModelInterface $poll, array $answers): PodiumResponse
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
    public function sendMessage(
        array $data,
        MembershipInterface $receiver,
        ?MessageParticipantModelInterface $replyTo = null
    ): PodiumResponse
    {
        return $this->podium->message->send($data, $this->ensureMembership(), $receiver, $replyTo);
    }

    /**
     * Deletes message.
     * @param int $id
     * @return PodiumResponse
     * @throws NoMembershipException
     * @throws ModelNotFoundException
     */
    public function removeMessage(int $id): PodiumResponse
    {
        return $this->podium->message->remove($id, $this->ensureMembership());
    }
}
