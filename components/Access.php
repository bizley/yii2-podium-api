<?php

namespace bizley\podium\api\components;

/**
 * Class Access
 * @package bizley\podium\api\components
 *
 * @property \bizley\podium\api\repositories\Permission $permissionRepo
 */
class Access extends Component
{
    const EVENT_BEFORE_GRANT = 'access.grant.before';
    const EVENT_AFTER_GRANT = 'access.grant.after';
    const EVENT_BEFORE_REVOKE = 'access.revoke.before';
    const EVENT_AFTER_REVOKE = 'access.revoke.after';

    private $_checkedPermissions = [];

    /**
     * Checks if member has got required permission
     * @param $member
     * @param $permission
     * @throws PermissionRequiredException
     */
    public function check($member, $permission)
    {
        if (!isset($this->_checkedPermissions[$member][$permission])) {
            $this->_checkedPermissions[$member][$permission] = $this->permissionRepo->check(['member_id' => $member, 'permission' => $permission]);
        }
        if (!$this->_checkedPermissions[$member][$permission]) {
            throw new PermissionRequiredException("Member ID $member has not been granted \"$permission\" permission!");
        }
    }

    /**
     * @return bool
     */
    public function beforeGrant()
    {
        $event = new PodiumEvent();
        $this->trigger(self::EVENT_BEFORE_GRANT, $event);
        return $event->isValid;
    }

    /**
     * @param $member
     * @param $permission
     * @return bool
     */
    public function grant($member, $permission)
    {
        if (!$this->beforeGrant()) {
            return false;
        }
        $result = $this->permissionRepo->store(['member_id' => $member, 'permission' => $permission]);

        $this->afterGrant();
        return $result;
    }

    /**
     *
     */
    public function afterGrant()
    {
        $this->trigger(self::EVENT_AFTER_GRANT);
    }

    /**
     * @return bool
     */
    public function beforeRevoke()
    {
        $event = new PodiumEvent();
        $this->trigger(self::EVENT_BEFORE_REVOKE, $event);
        return $event->isValid;
    }

    /**
     * @param $member
     * @param $permission
     * @return bool
     * @throws PermissionRequiredException
     * @throws \bizley\podium\api\repositories\RepoNotFoundException
     */
    public function revoke($member, $permission)
    {
        if (!$this->beforeRevoke()) {
            return false;
        }

        $this->check($member, $permission);
        $result = $this->permissionRepo->fetch(['member_id' => $member, 'permission' => $permission])->remove();

        $this->afterRevoke();
        return $result;
    }

    /**
     *
     */
    public function afterRevoke()
    {
        $this->trigger(self::EVENT_AFTER_REVOKE);
    }
}