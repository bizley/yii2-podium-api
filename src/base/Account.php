<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\AccountInterface;
use bizley\podium\api\interfaces\MemberModelInterface;
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
     */
    public $membershipHandler;

    /**
     * @var string|array|User user component handler
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
            $this->_membership = $class::findMembership($this->userHandler->id);
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
     * @param MemberModelInterface $target
     * @return bool
     */
    public function befriend(MemberModelInterface $target): bool
    {
        return $this->podium->member->befriend($this->membership, $target);
    }

    /**
     * @param MemberModelInterface $target
     * @return bool
     */
    public function unfriend(MemberModelInterface $target): bool
    {
        return $this->podium->member->unfriend($this->membership, $target);
    }

    /**
     * @param MemberModelInterface $target
     * @return bool
     */
    public function ignore(MemberModelInterface $target): bool
    {
        return $this->podium->member->ignore($this->membership, $target);
    }

    /**
     * @param MemberModelInterface $target
     * @return bool
     */
    public function unignore(MemberModelInterface $target): bool
    {
        return $this->podium->member->unignore($this->membership, $target);
    }
}
