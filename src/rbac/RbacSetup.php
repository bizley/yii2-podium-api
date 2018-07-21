<?php

declare(strict_types=1);

namespace bizley\podium\api\rbac;

use bizley\podium\api\enums\Permission;
use bizley\podium\api\enums\Role;
use bizley\podium\api\events\RbacEvent;
use yii\base\Component;
use yii\rbac\DbManager;

/**
 * Class RbacSetup
 * @package bizley\podium\api\rbac
 *
 * @property DbManager $manager
 */
class RbacSetup extends Component
{
    public const EVENT_BEFORE_SETUP = 'podium.rbac.setup.before';
    public const EVENT_AFTER_SETUP = 'podium.rbac.setup.after';

    private $_manager;

    /**
     * @param DbManager $manager
     * @param array $config
     */
    public function __construct(DbManager $manager, array $config = [])
    {
        $this->_manager = $manager;
        parent::__construct($config);
    }

    /**
     * @return DbManager
     */
    public function getManager(): DbManager
    {
        return $this->_manager;
    }

    /**
     * @return bool
     */
    public function beforeSetup(): bool
    {
        $event = new RbacEvent();
        $this->trigger(self::EVENT_BEFORE_SETUP, $event);

        return $event->canSetup;
    }

    /**
     * @return bool
     */
    public function run(): bool
    {
        if (!$this->beforeSetup()) {
            return false;
        }
        if ($this->createRules() && $this->createPermissions() && $this->createRoles()) {
            $this->afterSetup();
            return true;
        }
        return false;
    }

    public function afterSetup(): void
    {
        $this->trigger(self::EVENT_AFTER_SETUP);
    }

    private $_rules = [];

    /**
     * @return bool
     */
    protected function createRules(): bool
    {
        try {
            $this->_rules['group'] = new GroupRule();
            $this->getManager()->add($this->_rules['group']);

            $this->_rules['modify'] = new ModifyRule();
            $this->getManager()->add($this->_rules['modify']);

            return true;

        } catch (\Throwable $exc) {
            \Yii::error(['rbac.create.rule.exception', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            return false;
        }
    }

    private $_permissions = [];

    /**
     * @return bool
     */
    protected function createPermissions(): bool
    {
        try {
            $permissions = Permission::data();
            foreach ($permissions as $permission => $description) {
                $this->_permissions[$permission] = $this->getManager()->createPermission($permission);
                $this->_permissions[$permission]->description = $description;

                if (\in_array($permission, [
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
                    $this->_permissions[$permission]->ruleName = $this->_rules['modify']->name;
                } elseif (\in_array($permission, [
                        Permission::CATEGORY_VIEW,
                        Permission::FORUM_VIEW,
                        Permission::THREAD_VIEW,
                        Permission::POST_VIEW,
                        Permission::THREAD_SUBSCRIBE,
                    ], true)) {
                    $this->_permissions[$permission]->ruleName = $this->_rules['group']->name;
                }

                $this->getManager()->add($this->_permissions[$permission]);
            }
            return true;

        } catch (\Throwable $exc) {
            \Yii::error(['rbac.create.permission.exception', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            return false;
        }
    }

    /**
     * @return bool
     */
    protected function createRoles(): bool
    {
        try {
            $guest = $this->getManager()->createRole(Role::GUEST);
            $this->getManager()->add($guest);

            foreach ([
                    Permission::CATEGORY_VIEW,
                    Permission::FORUM_VIEW,
                    Permission::THREAD_VIEW,
                    Permission::POST_VIEW,
                    Permission::MEMBER_VIEW,
                    Permission::GROUP_VIEW,
                ] as $permission) {
                $this->getManager()->addChild($guest, $this->_permissions[$permission]);
            }

            $member = $this->getManager()->createRole(Role::MEMBER);
            $this->getManager()->add($member);
            $this->getManager()->addChild($member, $guest);

            foreach ([
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
                ] as $permission) {
                $this->getManager()->addChild($member, $this->_permissions[$permission]);
            }

            $moderator = $this->getManager()->createRole(Role::MODERATOR);
            $this->getManager()->add($moderator);
            $this->getManager()->addChild($moderator, $member);

            foreach ([
                    Permission::THREAD_LOCK,
                    Permission::THREAD_PIN,
                    Permission::THREAD_MOVE,
                    Permission::POST_MOVE,
                ] as $permission) {
                $this->getManager()->addChild($moderator, $this->_permissions[$permission]);
            }

            $admin = $this->getManager()->createRole(Role::ADMIN);
            $this->getManager()->add($admin);
            $this->getManager()->addChild($admin, $moderator);

            foreach ([
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
                ] as $permission) {
                $this->getManager()->addChild($admin, $this->_permissions[$permission]);
            }

            return true;

        } catch (\Throwable $exc) {
            \Yii::error(['rbac.create.role.exception', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
            return false;
        }
    }
}
