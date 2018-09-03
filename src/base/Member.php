<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\FriendshipInterface;
use bizley\podium\api\interfaces\GroupingInterface;
use bizley\podium\api\interfaces\IgnoringInterface;
use bizley\podium\api\interfaces\BanInterface;
use bizley\podium\api\interfaces\MemberInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RegistrationInterface;
use bizley\podium\api\interfaces\RemovableInterface;
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
 * @property GroupingInterface $grouping
 * @property IgnoringInterface $ignoring
 */
class Member extends PodiumComponent implements MemberInterface
{
    /**
     * @var string|array|MembershipInterface member handler
     * Component ID, class, configuration array, or instance of MembershipInterface.
     */
    public $memberHandler = \bizley\podium\api\models\member\Member::class;

    /**
     * @var string|array|RegistrationInterface registration handler
     * Component ID, class, configuration array, or instance of RegistrationInterface.
     */
    public $registrationHandler = \bizley\podium\api\models\member\Registration::class;

    /**
     * @var string|array|FriendshipInterface friendship handler
     * Component ID, class, configuration array, or instance of FriendshipInterface.
     */
    public $friendshipHandler = \bizley\podium\api\models\member\Friendship::class;

    /**
     * @var string|array|IgnoringInterface ignoring handler
     * Component ID, class, configuration array, or instance of IgnoringInterface.
     */
    public $ignoringHandler = \bizley\podium\api\models\member\Ignoring::class;

    /**
     * @var string|array|GroupingInterface grouping handler
     * Component ID, class, configuration array, or instance of GroupingInterface.
     */
    public $groupingHandler = \bizley\podium\api\models\member\Grouping::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->memberHandler = Instance::ensure($this->memberHandler, MembershipInterface::class);
        $this->registrationHandler = Instance::ensure($this->registrationHandler, RegistrationInterface::class);
        $this->friendshipHandler = Instance::ensure($this->friendshipHandler, FriendshipInterface::class);
        $this->ignoringHandler = Instance::ensure($this->ignoringHandler, IgnoringInterface::class);
        $this->groupingHandler = Instance::ensure($this->groupingHandler, GroupingInterface::class);
    }

    /**
     * @return FriendshipInterface
     */
    public function getFriendship(): FriendshipInterface
    {
        return new $this->friendshipHandler;
    }

    /**
     * Befriends target by a member,
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function befriend(MembershipInterface $member, MembershipInterface $target): PodiumResponse
    {
        $friendship = $this->getFriendship();
        $friendship->setMember($member);
        $friendship->setTarget($target);
        return $friendship->befriend();
    }

    /**
     * Unfriends target by a member.
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function unfriend(MembershipInterface $member, MembershipInterface $target): PodiumResponse
    {
        $friendship = $this->getFriendship();
        $friendship->setMember($member);
        $friendship->setTarget($target);
        return $friendship->unfriend();
    }

    /**
     * @return IgnoringInterface
     */
    public function getIgnoring(): IgnoringInterface
    {
        return new $this->ignoringHandler;
    }

    /**
     * Ignores target by a member.
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function ignore(MembershipInterface $member, MembershipInterface $target): PodiumResponse
    {
        $ignoring = $this->getIgnoring();
        $ignoring->setMember($member);
        $ignoring->setTarget($target);
        return $ignoring->ignore();
    }

    /**
     * Unignores target by a member.
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function unignore(MembershipInterface $member, MembershipInterface $target): PodiumResponse
    {
        $ignoring = $this->getIgnoring();
        $ignoring->setMember($member);
        $ignoring->setTarget($target);
        return $ignoring->unignore();
    }

    /**
     * @return RegistrationInterface
     */
    public function getRegistration(): RegistrationInterface
    {
        return new $this->registrationHandler;
    }

    /**
     * Registers member.
     * @param array $data
     * @return PodiumResponse
     */
    public function register(array $data): PodiumResponse
    {
        $registration = $this->getRegistration();
        if (!$registration->loadData($data)) {
            return PodiumResponse::error();
        }
        return $registration->register();
    }

    /**
     * Deletes member.
     * @param RemovableInterface $memberRemover
     * @return PodiumResponse
     */
    public function remove(RemovableInterface $memberRemover): PodiumResponse
    {
        return $memberRemover->remove();
    }

    /**
     * @param int $id
     * @return MembershipInterface|ModelInterface|null
     */
    public function getMemberById(int $id): ?MembershipInterface
    {
        $membership = $this->memberHandler;
        return $membership::findById($id);
    }

    /**
     * @param int|string $id
     * @return MembershipInterface|null
     */
    public function getMemberByUserId($id): ?MembershipInterface
    {
        $membership = $this->memberHandler;
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
        $membership = $this->memberHandler;
        return $membership::findByFilter($filter, $sort, $pagination);
    }

    /**
     * Updates member.
     * @param ModelFormInterface $memberForm
     * @param array $data
     * @return PodiumResponse
     */
    public function edit(ModelFormInterface $memberForm, array $data): PodiumResponse
    {
        if (!$memberForm->loadData($data)) {
            return PodiumResponse::error();
        }
        return $memberForm->edit();
    }

    /**
     * Bans member.
     * @param BanInterface $member
     * @return PodiumResponse
     */
    public function ban(BanInterface $member): PodiumResponse
    {
        return $member->ban();
    }

    /**
     * Unbans member.
     * @param BanInterface $member
     * @return PodiumResponse
     */
    public function unban(BanInterface $member): PodiumResponse
    {
        return $member->unban();
    }

    /**
     * @return GroupingInterface
     */
    public function getGrouping(): GroupingInterface
    {
        return new $this->groupingHandler;
    }

    /**
     * Adds member to a group.
     * @param MembershipInterface $member
     * @param ModelInterface $group
     * @return PodiumResponse
     */
    public function join(MembershipInterface $member, ModelInterface $group): PodiumResponse
    {
        $grouping = $this->getGrouping();
        $grouping->setMember($member);
        $grouping->setGroup($group);

        return $grouping->join();
    }

    /**
     * Removes member from a group.
     * @param MembershipInterface $member
     * @param ModelInterface $group
     * @return PodiumResponse
     */
    public function leave(MembershipInterface $member, ModelInterface $group): PodiumResponse
    {
        $grouping = $this->getGrouping();
        $grouping->setMember($member);
        $grouping->setGroup($group);

        return $grouping->leave();
    }
}
