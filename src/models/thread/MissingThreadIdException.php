<?php

declare(strict_types=1);

namespace bizley\podium\api\models\thread;

use yii\base\Exception;

/**
 * Class MissingThreadIdException
 * @package bizley\podium\api\models\thread
 */
class MissingThreadIdException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName(): string
    {
        return 'Missing Thread Id';
    }
}
