<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\FriendshipInterface;
use bizley\podium\api\interfaces\IgnoringInterface;
use bizley\podium\api\interfaces\MemberInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RegistrationInterface;
use bizley\podium\api\models\member\Friendship;
use bizley\podium\api\models\member\Ignoring;
use bizley\podium\api\models\member\Registration;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;
use yii\di\Instance;

/**
 * Class Member
 * @package bizley\podium\api\base
 *
 * @property FriendshipInterface $friendship
 * @property RegistrationInterface $registration
 * @property MembershipInterface $membership
 * @property IgnoringInterface $ignoring
 */
class Member extends PodiumComponent implements MemberInterface
{
    /**
     * @var string|array|MembershipInterface
     * Component ID, class, configuration array, or instance of MembershipInterface.
     */
    public $memberHandler = \bizley\podium\api\models\member\Member::class;

    /**
     * @var string|array|RegistrationInterface
     * Component ID, class, configuration array, or instance of RegistrationInterface.
     */
    public $registrationHandler = Registration::class;

    /**
     * @var string|array|FriendshipInterface
     * Component ID, class, configuration array, or instance of FriendshipInterface.
     */
    public $friendshipHandler = Friendship::class;

    /**
     * @var string|array|IgnoringInterface
     * Component ID, class, configuration array, or instance of IgnoringInterface.
     */
    public $ignoringHandler = Ignoring::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->memberHandler = Instance::ensure($this->memberHandler, MembershipInterface::class);
        $this->registrationHandler = Instance::ensure($this->registrationHandler, RegistrationInterface::class);
        $this->friendshipHandler = Instance::ensure($this->friendshipHandler, FriendshipInterface::class);
        $this->ignoringHandler = Instance::ensure($this->ignoringHandler, IgnoringInterface::class);
    }

    /**
     * @return MembershipInterface
     */
    public function getMembership(): MembershipInterface
    {
        return $this->memberHandler;
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
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return bool
     */
    public function befriend(MembershipInterface $member, MembershipInterface $target): bool
    {
        $friendship = $this->getFriendship();
        $friendship->setMember($member);
        $friendship->setTarget($target);
        return $friendship->befriend();
    }

    /**
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return bool
     */
    public function unfriend(MembershipInterface $member, MembershipInterface $target): bool
    {
        $friendship = $this->getFriendship();
        $friendship->setMember($member);
        $friendship->setTarget($target);
        return $friendship->unfriend();
    }

    /**
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return bool
     */
    public function ignore(MembershipInterface $member, MembershipInterface $target): bool
    {
        $ignoring = $this->getIgnoring();
        $ignoring->setMember($member);
        $ignoring->setTarget($target);
        return $ignoring->ignore();
    }

    /**
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return bool
     */
    public function unignore(MembershipInterface $member, MembershipInterface $target): bool
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

    /**
     * @param int $id
     * @return MembershipInterface|ModelInterface|null
     */
    public function getMemberById(int $id): ?MembershipInterface
    {
        $membership = $this->getMembership();
        return $membership::findById($id);
    }

    /**
     * @param int|string $id
     * @return MembershipInterface|null
     */
    public function getMemberByUserId($id): ?MembershipInterface
    {
        $membership = $this->getMembership();
        return $membership::findByUserId($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getMembers(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $membership = $this->getMembership();
        return $membership::findByFilter($filter, $sort, $pagination);
    }
}
