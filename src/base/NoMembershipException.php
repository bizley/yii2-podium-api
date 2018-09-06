<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use yii\base\Exception;

/**
 * Class NoMembershipException
 * @package bizley\podium\api\base
 */
class NoMembershipException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName(): string
    {
        return 'No Membership Exception';
    }
}
