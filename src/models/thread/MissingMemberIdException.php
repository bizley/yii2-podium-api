<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use yii\base\Exception;

/**
 * Class MissingMemberIdException
 * @package bizley\podium\api\models\thread
 */
class MissingMemberIdException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName(): string
    {
        return 'Missing Member Id';
    }
}
