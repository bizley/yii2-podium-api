<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\FriendshipInterface;
use bizley\podium\api\interfaces\IgnoringInterface;
use bizley\podium\api\interfaces\MemberComponentInterface;
use bizley\podium\api\interfaces\MemberModelInterface;
use bizley\podium\api\interfaces\RegistrationInterface;
use yii\di\Instance;

/**
 * Class Member
 * @package bizley\podium\api\base
 *
 * @property FriendshipInterface $friendship
 * @property IgnoringInterface $ignoring
 */
class Member extends PodiumComponent implements MemberComponentInterface
{
    /**
     * @var string|array|RegistrationInterface
     */
    public $registrationHandler;

    /**
     * @var string|array|FriendshipInterface
     */
    public $friendshipHandler;

    /**
     * @var string|array|IgnoringInterface
     */
    public $ignoringHandler;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->registrationHandler = Instance::ensure($this->registrationHandler, RegistrationInterface::class);
        $this->friendshipHandler = Instance::ensure($this->friendshipHandler, FriendshipInterface::class);
        $this->ignoringHandler = Instance::ensure($this->ignoringHandler, IgnoringInterface::class);
    }

    /**
     * @return RegistrationInterface
     */
    public function getRegistration(): RegistrationInterface
    {
        return $this->registrationHandler;
    }

    /**
     * @return FriendshipInterface
     */
    public function getFriendship(): FriendshipInterface
    {
        return $this->friendshipHandler;
    }

    /**
     * @return IgnoringInterface
     */
    public function getIgnoring(): IgnoringInterface
    {
        return $this->ignoringHandler;
    }

    /**
     * @param MemberModelInterface $member
     * @param MemberModelInterface $target
     * @return bool
     */
    public function befriend(MemberModelInterface $member, MemberModelInterface $target): bool
    {
        $friendship = $this->getFriendship();
        $friendship->setMember($member);
        $friendship->setTarget($target);
        return $friendship->befriend();
    }

    /**
     * @param MemberModelInterface $member
     * @param MemberModelInterface $target
     * @return bool
     */
    public function unfriend(MemberModelInterface $member, MemberModelInterface $target): bool
    {
        $friendship = $this->getFriendship();
        $friendship->setMember($member);
        $friendship->setTarget($target);
        return $friendship->unfriend();
    }

    /**
     * @param MemberModelInterface $member
     * @param MemberModelInterface $target
     * @return bool
     */
    public function ignore(MemberModelInterface $member, MemberModelInterface $target): bool
    {
        $ignoring = $this->getIgnoring();
        $ignoring->setMember($member);
        $ignoring->setTarget($target);
        return $ignoring->ignore();
    }

    /**
     * @param MemberModelInterface $member
     * @param MemberModelInterface $target
     * @return bool
     */
    public function unignore(MemberModelInterface $member, MemberModelInterface $target): bool
    {
        $ignoring = $this->getIgnoring();
        $ignoring->setMember($member);
        $ignoring->setTarget($target);
        return $ignoring->unignore();
    }

    /**
     * @param array $data
     * @return bool
     */
    public function register(array $data): bool
    {
        $registration = $this->getRegistration();
        if (!$registration->loadData($data)) {
            return false;
        }
        return $registration->register();
    }
}
