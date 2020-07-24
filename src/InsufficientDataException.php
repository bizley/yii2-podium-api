<?php

declare(strict_types=1);

namespace bizley\podium\api;

use yii\base\Exception;

/**
 * Class InsufficientDataException
 * @package bizley\podium\api
 */
final class InsufficientDataException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName(): string
    {
        return 'Insufficient Data Exception';
    }
}
