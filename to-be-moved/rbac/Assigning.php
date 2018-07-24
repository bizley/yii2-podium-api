<?php

declare(strict_types=1);

namespace bizley\podium\api\rbac;

use bizley\podium\api\events\RoleEvent;
use bizley\podium\api\interfaces\AssigningInterface;
use bizley\podium\api\interfaces\MembershipInterface;
use Throwable;
use Yii;
use yii\base\Component;
use yii\rbac\Assignment;
use yii\rbac\DbManager;
use yii\rbac\Permission;
use yii\rbac\Role;

/**
 * Class Assigning
 * @package bizley\podium\api\rbac
 *
 * TODO: move to Podium client
 */
class Assigning extends Component implements AssigningInterface
{
    public const EVENT_BEFORE_SWITCH = 'podium.assigning.switch.before';
    public const EVENT_AFTER_SWITCH = 'podium.assigning.switch.after';

    /**
     * @var DbManager
     */
    private $_manager;

    /**
     * @param DbManager $manager
     */
    public function setManager(DbManager $manager): void
    {
        $this->_manager = $manager;
    }

    private $_memberId;

    /**
     * @param MembershipInterface $member
     */
    public function setMember(MembershipInterface $member): void
    {
        $this->_memberId = $member->getId();
    }

    private $_role;

    /**
     * @param Role|Permission $role
     */
    public function setRole($role): void
    {
        $this->_role = $role;
    }

    /**
     * @return bool
     */
    public function beforeSwitch(): bool
    {
        $event = new RoleEvent();
        $this->trigger(self::EVENT_BEFORE_SWITCH, $event);

        return $event->canAssign;
    }

    /**
     * @return bool
     */
    public function switch(): bool
    {
        if (!$this->beforeSwitch()) {
            return false;
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $currentRoles = $this->_manager->getRolesByUser($this->_memberId);
            foreach ($currentRoles as $name => $role) {
                if (\in_array($name, \bizley\podium\api\enums\Role::keys(), true)) {
                    if (!$this->_manager->revoke($role, $this->_memberId)) {
                        throw new RoleRevokeException("Error while revoking '{$name}' role from member of ID {$this->_memberId}");
                    }
                    // by default only one role is allowed in Podium
                    break;
                }
            }

            $this->afterSwitch($this->_manager->assign($this->_role, $this->_memberId));

            $transaction->commit();
            return true;

        } catch (Throwable $exc) {
            Yii::error(['assigning.assign.exception', $exc->getMessage(), $exc->getTraceAsString()]);
            try {
                $transaction->rollBack();
            } catch (Throwable $rollbackExc) {
                Yii::error(['assigning.assign.rollback.exception', $rollbackExc->getMessage(), $rollbackExc->getTraceAsString()]);
            }
        }
        return false;
    }

    /**
     * @param Assignment $assignment
     */
    public function afterSwitch(Assignment $assignment): void
    {
        $this->trigger(self::EVENT_AFTER_SWITCH, new RoleEvent([
            'assignment' => $assignment
        ]));
    }
}
