<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\BefrienderInterface;
use bizley\podium\api\interfaces\GrouperInterface;
use bizley\podium\api\interfaces\IgnorerInterface;
use bizley\podium\api\interfaces\BanisherInterface;
use bizley\podium\api\interfaces\MemberInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RegistererInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
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
class Member extends Component implements MemberInterface
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
     * @var string|array|RegistererInterface member registerer handler
     * Component ID, class, configuration array, or instance of RegistererInterface.
     */
    public $registererHandler = \bizley\podium\api\models\member\MemberRegisterer::class;

    /**
     * @var string|array|BefrienderInterface member befriender handler
     * Component ID, class, configuration array, or instance of BefrienderInterface.
     */
    public $befrienderHandler = \bizley\podium\api\models\member\MemberBefriender::class;

    /**
     * @var string|array|IgnorerInterface member ignorer handler
     * Component ID, class, configuration array, or instance of IgnorerInterface.
     */
    public $ignorerHandler = \bizley\podium\api\models\member\MemberIgnorer::class;

    /**
     * @var string|array|GrouperInterface member grouper handler
     * Component ID, class, configuration array, or instance of GrouperInterface.
     */
    public $grouperHandler = \bizley\podium\api\models\member\MemberGrouper::class;

    /**
     * @var string|array|RemoverInterface member remover handler
     * Component ID, class, configuration array, or instance of RemoverInterface.
     */
    public $removerHandler = \bizley\podium\api\models\member\MemberRemover::class;

    /**
     * @var string|array|BanisherInterface member banisher handler
     * Component ID, class, configuration array, or instance of BanisherInterface.
     */
    public $banisherHandler = \bizley\podium\api\models\member\MemberBanisher::class;

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->modelHandler = Instance::ensure($this->modelHandler, MembershipInterface::class);
        $this->formHandler = Instance::ensure($this->formHandler, ModelFormInterface::class);
        $this->registererHandler = Instance::ensure($this->registererHandler, RegistererInterface::class);
        $this->befrienderHandler = Instance::ensure($this->befrienderHandler, BefrienderInterface::class);
        $this->ignorerHandler = Instance::ensure($this->ignorerHandler, IgnorerInterface::class);
        $this->grouperHandler = Instance::ensure($this->grouperHandler, GrouperInterface::class);
        $this->removerHandler = Instance::ensure($this->removerHandler, RemoverInterface::class);
        $this->banisherHandler = Instance::ensure($this->banisherHandler, BanisherInterface::class);
    }

    /**
     * @return RegistererInterface
     */
    public function getRegisterer(): RegistererInterface
    {
        return new $this->registererHandler;
    }

    /**
     * Registers member.
     * @param array $data
     * @return PodiumResponse
     */
    public function register(array $data): PodiumResponse
    {
        $registration = $this->getRegisterer();

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
     * @param int $id
     * @return ModelFormInterface|null
     */
    public function getForm(int $id): ?ModelFormInterface
    {
        $handler = $this->formHandler;

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
     * @return BefrienderInterface
     */
    public function getBefriender(): BefrienderInterface
    {
        return new $this->befrienderHandler;
    }

    /**
     * Befriends target by a member,
     * @param MembershipInterface $member
     * @param MembershipInterface $target
     * @return PodiumResponse
     */
    public function befriend(MembershipInterface $member, MembershipInterface $target): PodiumResponse
    {
        $friendship = $this->getBefriender();

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
        $friendship = $this->getBefriender();

        $friendship->setMember($member);
        $friendship->setTarget($target);

        return $friendship->unfriend();
    }

    /**
     * @return IgnorerInterface
     */
    public function getIgnorer(): IgnorerInterface
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
     * @return BanisherInterface|null
     */
    public function getBanisher(int $id): ?BanisherInterface
    {
        $handler = $this->banisherHandler;

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
        $memberBanner = $this->getBanisher($id);

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
        $memberBanner = $this->getBanisher($id);

        if ($memberBanner === null) {
            throw new ModelNotFoundException('Member of given ID can not be found.');
        }

        return $memberBanner->unban();
    }

    /**
     * @return GrouperInterface
     */
    public function getGrouper(): GrouperInterface
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
     * @return RemoverInterface|null
     */
    public function getRemover(int $id): ?RemoverInterface
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
