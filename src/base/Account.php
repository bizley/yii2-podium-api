<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\AccountInterface;
use bizley\podium\api\interfaces\MembershipInterface;
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
     * @var string|array|MembershipInterface
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
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function befriend(MembershipInterface $target): PodiumResponse
    {
        return $this->podium->member->befriend($this->membership, $target);
    }

    /**
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function unfriend(MembershipInterface $target): PodiumResponse
    {
        return $this->podium->member->unfriend($this->membership, $target);
    }

    /**
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function ignore(MembershipInterface $target): PodiumResponse
    {
        return $this->podium->member->ignore($this->membership, $target);
    }

    /**
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function unignore(MembershipInterface $target): PodiumResponse
    {
        return $this->podium->member->unignore($this->membership, $target);
    }

    /**
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function thumbUp(ModelInterface $post): PodiumResponse
    {
        return $this->podium->post->thumbUp($this->membership, $post);
    }

    /**
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function thumbDown(ModelInterface $post): PodiumResponse
    {
        return $this->podium->post->thumbDown($this->membership, $post);
    }

    /**
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function thumbReset(ModelInterface $post): PodiumResponse
    {
        return $this->podium->post->thumbReset($this->membership, $post);
    }

    /**
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function subscribe(ModelInterface $thread): PodiumResponse
    {
        return $this->podium->thread->subscribe($this->membership, $thread);
    }

    /**
     * @param ModelInterface $thread
     * @return PodiumResponse
     */
    public function unsubscribe(ModelInterface $thread): PodiumResponse
    {
        return $this->podium->thread->unsubscribe($this->membership, $thread);
    }

    /**
     * @param ModelInterface $post
     * @return PodiumResponse
     */
    public function mark(ModelInterface $post): PodiumResponse
    {
        return $this->podium->thread->mark($this->membership, $post);
    }

    /**
     * @param ModelInterface $group
     * @return PodiumResponse
     */
    public function join(ModelInterface $group): PodiumResponse
    {
        return $this->podium->member->join($this->membership, $group);
    }

    /**
     * @param ModelInterface $group
     * @return PodiumResponse
     */
    public function leave(ModelInterface $group): PodiumResponse
    {
        return $this->podium->member->leave($this->membership, $group);
    }

    /**
     * Votes in poll.
     * @param PollModelInterface $poll
     * @param PollAnswerModelInterface[] $answers
     * @return PodiumResponse
     */
    public function vote(PollModelInterface $poll, array $answers): PodiumResponse
    {
        return $this->podium->poll->vote($this->membership, $poll, $answers);
    }
}
