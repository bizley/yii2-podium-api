<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\models\Member;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\web\User;

/**
 * Class Membership
 * @package bizley\podium\api\base
 *
 * @property null|MembershipInterface $membership
 * @property null|int $id
 */
class Membership extends Component
{
    /**
     * @var string member class
     * Class must implement MembershipInterface.
     */
    public $memberClass = Member::class;

    /**
     * @var string|array|User user component handler
     * This can be component name, configuration array, or instance of User class.
     */
    public $user = 'user';

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        if ($this->memberClass === null) {
            throw new InvalidConfigException('Membership::memberClass must be set.');
        }
        $this->user = Instance::ensure($this->user, User::class);
    }

    private $_membership = false;

    /**
     * @return MembershipInterface|null
     */
    public function getMembership(): ?MembershipInterface
    {
        if ($this->_membership === false) {
            /* @var $class MembershipInterface */
            $class = $this->memberClass;
            $this->_membership = $class::findMembership($this->user->id);
        }
        return $this->_membership;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->getMembership()->getId() ?? null;
    }
}
