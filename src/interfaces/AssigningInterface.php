<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use yii\rbac\DbManager;
use yii\rbac\Permission;
use yii\rbac\Role;

/**
 * Interface AssigningInterface
 * @package bizley\podium\api\interfaces
 */
interface AssigningInterface
{
    /**
     * @param DbManager $manager
     */
    public function setManager(DbManager $manager): void;

    /**
     * @param MemberModelInterface $member
     */
    public function setMember(MemberModelInterface $member): void;

    /**
     * @param Role|Permission $role
     */
    public function setRole($role): void;

    /**
     * Switches current member role to new one.
     * @return bool
     */
    public function switch(): bool;
}
