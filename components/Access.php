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
}