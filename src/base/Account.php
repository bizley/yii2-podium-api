<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\AccountInterface;
use bizley\podium\api\interfaces\MembershipInterface;
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
    public $membershipHandler = \bizley\podium\api\models\Member::class;

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
            $this->_membership = $class::findMemberByUserId($this->userHandler->id);
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
     * @return bool
     */
    public function befriend(MembershipInterface $target): bool
    {
        return $this->podium->member->befriend($this->membership, $target);
    }

    /**
     * @param MembershipInterface $target
     * @return bool
     */
    public function unfriend(MembershipInterface $target): bool
    {
        return $this->podium->member->unfriend($this->membership, $target);
    }

    /**
     * @param MembershipInterface $target
     * @return bool
     */
    public function ignore(MembershipInterface $target): bool
    {
        return $this->podium->member->ignore($this->membership, $target);
    }

    /**
     * @param MembershipInterface $target
     * @return bool
     */
    public function unignore(MembershipInterface $target): bool
    {
        return $this->podium->member->unignore($this->membership, $target);
    }

    /**
     * @param string $permissionName
     * @param array $params
     * @return bool
     */
    public function can(string $permissionName, array $params = []): bool
    {
        return $this->podium->access->check($this->membership, $permissionName, $params);
    }
}
