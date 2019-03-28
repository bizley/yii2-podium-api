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
use yii\helpers\ArrayHelper;

/**
 * Class Member
 * @package bizley\podium\api\base
 */
class Member extends PodiumComponent implements MemberInterface
{
    /**
     * @var string|array|MembershipInterface member handler
     * Component ID, class, configuration array, or instance of MembershipInterface.
     */
    public $modelHandler = \bizley\podium\api\models\member\Member::class;

    /**
     * @var string|array|ModelFormInterface member form handler
     * Component ID, class, configuration array, or instance of ModelFormInterface.
     */
    public $formHandler = \bizley\podium\api\models\member\MemberForm::class;

    /**
     * @var string|array|RegistrationInterface registration handler
     * Component ID, class, configuration array, or instance of RegistrationInterface.
     */
    public $registrationHandler = \bizley\podium\api\models\member\Registration::class;

    /**
     * @var string|array|FriendshipInterface friendship handler
     * Component ID, class, configuration array, or instance of FriendshipInterface.
     */
    public $friendshipHandler = \bizley\podium\api\models\member\MemberFriendship::class;

    /**
     * @var string|array|IgnoringInterface ignoring handler
     * Component ID, class, configuration array, or instance of IgnoringInterface.
     */
    public $ignorerHandler = \bizley\podium\api\models\member\MemberIgnorer::class;

    /**
     * @var string|array|GroupingInterface grouping handler
     * Component ID, class, configuration array, or instance of GroupingInterface.
     */
    public $grouperHandler = \bizley\podium\api\models\member\MemberGrouper::class;

    /**
     * @var string|array|RemovableInterface member remover handler
     * Component ID, class, configuration array, or instance of RemovableInterface.
     */
    public $removerHandler = \bizley\podium\api\models\member\MemberRemover::class;

    /**
     * @var string|array|BanInterface member banner handler
     * Component ID, class, configuration array, or instance of BanInterface.
     */
    public $bannerHandler = \bizley\podium\api\models\member\MemberBanner::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->modelHandler = Instance::ensure($this->modelHandler, MembershipInterface::class);
        $this->formHandler = Instance::ensure($this->formHandler, ModelFormInterface::class);
        $this->registrationHandler = Instance::ensure($this->registrationHandler, RegistrationInterface::class);
        $this->friendshipHandler = Instance::ensure($this->friendshipHandler, FriendshipInterface::class);
        $this->ignorerHandler = Instance::ensure($this->ignorerHandler, IgnoringInterface::class);
        $this->grouperHandler = Instance::ensure($this->grouperHandler, GroupingInterface::class);
        $this->removerHandler = Instance::ensure($this->removerHandler, RemovableInterface::class);
        $this->bannerHandler = Instance::ensure($this->bannerHandler, BanInterface::class);
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
     * @param int $id
     * @return MembershipInterface|null
     */
    public function getById(int $id): ?MembershipInterface
    {
        $membership = $this->modelHandler;

        return $membership::findById($id);
    }

    /**
     * @param int|string $id
     * @return MembershipInterface|null
     */
    public function getByUserId($id): ?MembershipInterface
    {
        $membership = $this->modelHandler;

        return $membership::findByUserId($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getAll(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $membership = $this->modelHandler;

        return $membership::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @param int|null $id
     * @return ModelFormInterface|null
     */
    public function getForm(?int $id = null): ?ModelFormInterface
    {
        $handler = $this->formHandler;

        if ($id === null) {
            return new $handler;
        }

        return $handler::findById($id);
    }

    /**
     * Updates member.
     * @param array $data
     * @return PodiumResponse
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function edit(array $data): PodiumResponse
    {
        $id = ArrayHelper::remove($data, 'id');

        if ($id === null) {
            throw new InsufficientDataException('ID key is missing.');
        }

        $memberForm = $this->getForm((int)$id);

        if ($memberForm === null) {
            throw new ModelNotFoundException('Member of given ID can not be found.');
        }

        if (!$memberForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $memberForm->edit();
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
    public function getIgnorer(): IgnoringInterface
    {
        return new $this->ignorerHandler;
    }

    /**
     * Ignores target by a member.
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function ignore(MembershipInterface $member, MembershipInterface $target): PodiumResponse
    {
        $ignoring = $this->getIgnorer();

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
        $ignoring = $this->getIgnorer();

        $ignoring->setMember($member);
        $ignoring->setTarget($target);

        return $ignoring->unignore();
    }

    /**
     * @param int $id
     * @return BanInterface|null
     */
    public function getBanner(int $id): ?BanInterface
    {
        $handler = $this->bannerHandler;

        return $handler::findById($id);
    }

    /**
     * Bans member.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function ban(int $id): PodiumResponse
    {
        $memberBanner = $this->getBanner($id);

        if ($memberBanner === null) {
            throw new ModelNotFoundException('Member of given ID can not be found.');
        }

        return $memberBanner->ban();
    }

    /**
     * Unbans member.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function unban(int $id): PodiumResponse
    {
        $memberBanner = $this->getBanner($id);

        if ($memberBanner === null) {
            throw new ModelNotFoundException('Member of given ID can not be found.');
        }

        return $memberBanner->unban();
    }

    /**
     * @return GroupingInterface
     */
    public function getGrouper(): GroupingInterface
    {
        return new $this->grouperHandler;
    }

    /**
     * Adds member to a group.
     * @param MembershipInterface $member
     * @param ModelInterface $group
     * @return PodiumResponse
     */
    public function join(MembershipInterface $member, ModelInterface $group): PodiumResponse
    {
        $grouping = $this->getGrouper();

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
        $grouping = $this->getGrouper();

        $grouping->setMember($member);
        $grouping->setGroup($group);

        return $grouping->leave();
    }

    /**
     * @param int $id
     * @return RemovableInterface|null
     */
    public function getRemover(int $id): ?RemovableInterface
    {
        $handler = $this->removerHandler;

        return $handler::findById($id);
    }

    /**
     * Deletes member.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function remove(int $id): PodiumResponse
    {
        $memberRemover = $this->getRemover($id);

        if ($memberRemover === null) {
            throw new ModelNotFoundException('Member of given ID can not be found.');
        }

        return $memberRemover->remove();
    }
}
