<?php

declare(strict_types=1);

namespace bizley\podium\api\rbac;

use yii\rbac\Item;
use yii\rbac\Rule;

/**
 * Class ModifyRule
 * @package bizley\podium\api\rbac
 */
class ModifyRule extends Rule
{
    /**
     * @var string
     */
    public $name = 'can.modify';

    /**
     * @param int $member
     * @param Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to CheckAccessInterface::checkAccess().
     * @return bool a value indicating whether the rule permits the auth item it is associated with.
     */
    public function execute($member, $item, $params): bool // BC definition
    {
        return isset($params['item']) ? $params['item']->isMod() : false; // TODO
    }
}
