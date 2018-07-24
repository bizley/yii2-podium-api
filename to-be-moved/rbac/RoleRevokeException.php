<?php

declare(strict_types=1);

namespace bizley\podium\api\rbac;

use yii\base\Exception;

/**
 * Class RoleRevokeException
 * @package bizley\podium\api\rbac
 *
 * TODO: move to Podium client
 */
class RoleRevokeException extends Exception
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Role Revoke Exception';
    }
}
