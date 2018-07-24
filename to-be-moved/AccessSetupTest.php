<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\Permission;
use bizley\podium\api\enums\Role;
use bizley\podium\api\rbac\GroupRule;
use bizley\podium\api\rbac\ModifyRule;
use bizley\podium\tests\DbTestCase;

/**
 * Class AccessSetupTest
 * @package bizley\podium\tests\base
 */
class AccessSetupTest extends DbTestCase
{
    public function testRbacSetup(): void
    {
        $this->assertTrue($this->podium()->access->setDefault());
        $this->assertInstanceOf(GroupRule::class, $this->podium()->access->getRule('is.group.member'));
        $this->assertInstanceOf(ModifyRule::class, $this->podium()->access->getRule('can.modify'));
        foreach (Permission::keys() as $permissionName) {
            $this->assertInstanceOf(\yii\rbac\Permission::class, $this->podium()->access->getPermission($permissionName));
            if (\in_array($permissionName, [
                    Permission::POST_DELETE,
                    Permission::POST_UPDATE,
                    Permission::POST_MOVE,
                    Permission::THREAD_UPDATE,
                    Permission::THREAD_DELETE,
                    Permission::THREAD_LOCK,
                    Permission::THREAD_MOVE,
                    Permission::THREAD_PIN,
                    Permission::POLL_UPDATE,
                    Permission::POLL_DELETE,
                ], true)) {
                $this->assertEquals('can.modify', $this->podium()->access->getPermission($permissionName)->ruleName);
            }
            if (\in_array($permissionName, [
                    Permission::CATEGORY_VIEW,
                    Permission::FORUM_VIEW,
                    Permission::THREAD_VIEW,
                    Permission::POST_VIEW,
                    Permission::THREAD_SUBSCRIBE,
                ], true)) {
                $this->assertEquals('is.group.member', $this->podium()->access->getPermission($permissionName)->ruleName);
            }
        }
        $rolePermissions = [
            Role::GUEST => [
                Permission::CATEGORY_VIEW,
                Permission::FORUM_VIEW,
                Permission::THREAD_VIEW,
                Permission::POST_VIEW,
                Permission::MEMBER_VIEW,
                Permission::GROUP_VIEW,
            ],
            Role::MEMBER => [
                Permission::CATEGORY_VIEW,
                Permission::FORUM_VIEW,
                Permission::THREAD_VIEW,
                Permission::POST_VIEW,
                Permission::MEMBER_VIEW,
                Permission::GROUP_VIEW,
                Permission::THREAD_CREATE,
                Permission::THREAD_UPDATE,
                Permission::THREAD_DELETE,
                Permission::THREAD_SUBSCRIBE,
                Permission::POST_CREATE,
                Permission::POST_UPDATE,
                Permission::POST_DELETE,
                Permission::POLL_VOTE,
                Permission::POLL_CREATE,
                Permission::POLL_UPDATE,
                Permission::POLL_DELETE,
                Permission::MEMBER_BEFRIEND,
            ],
            Role::MODERATOR => [
                Permission::CATEGORY_VIEW,
                Permission::FORUM_VIEW,
                Permission::THREAD_VIEW,
                Permission::POST_VIEW,
                Permission::MEMBER_VIEW,
                Permission::GROUP_VIEW,
                Permission::THREAD_CREATE,
                Permission::THREAD_UPDATE,
                Permission::THREAD_DELETE,
                Permission::THREAD_SUBSCRIBE,
                Permission::POST_CREATE,
                Permission::POST_UPDATE,
                Permission::POST_DELETE,
                Permission::POLL_VOTE,
                Permission::POLL_CREATE,
                Permission::POLL_UPDATE,
                Permission::POLL_DELETE,
                Permission::MEMBER_BEFRIEND,
                Permission::THREAD_LOCK,
                Permission::THREAD_PIN,
                Permission::THREAD_MOVE,
                Permission::POST_MOVE,
            ],
            Role::ADMIN => [
                Permission::CATEGORY_VIEW,
                Permission::FORUM_VIEW,
                Permission::THREAD_VIEW,
                Permission::POST_VIEW,
                Permission::MEMBER_VIEW,
                Permission::GROUP_VIEW,
                Permission::THREAD_CREATE,
                Permission::THREAD_UPDATE,
                Permission::THREAD_DELETE,
                Permission::THREAD_SUBSCRIBE,
                Permission::POST_CREATE,
                Permission::POST_UPDATE,
                Permission::POST_DELETE,
                Permission::POLL_VOTE,
                Permission::POLL_CREATE,
                Permission::POLL_UPDATE,
                Permission::POLL_DELETE,
                Permission::MEMBER_BEFRIEND,
                Permission::THREAD_LOCK,
                Permission::THREAD_PIN,
                Permission::THREAD_MOVE,
                Permission::POST_MOVE,
                Permission::CATEGORY_CREATE,
                Permission::CATEGORY_UPDATE,
                Permission::CATEGORY_DELETE,
                Permission::FORUM_CREATE,
                Permission::FORUM_UPDATE,
                Permission::FORUM_DELETE,
                Permission::FORUM_MOVE,
                Permission::GROUP_CREATE,
                Permission::GROUP_UPDATE,
                Permission::GROUP_DELETE,
                Permission::MEMBER_BAN,
                Permission::MEMBER_PROMOTE,
                Permission::MEMBER_DELETE,
            ],
        ];
        foreach (Role::keys() as $roleName) {
            $this->assertInstanceOf(\yii\rbac\Role::class, $this->podium()->access->getRole($roleName));
            $perms = array_keys($this->podium()->access->getPermissionsByRole($roleName));
            sort($rolePermissions[$roleName]);
            sort($perms);
            $this->assertEquals($rolePermissions[$roleName], $perms);
        }
    }
}
