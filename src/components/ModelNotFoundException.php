<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use yii\base\Exception;

/**
 * Class ModelNotFoundException
 * @package bizley\podium\api\base
 */
class ModelNotFoundException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName(): string
    {
        return 'Model Not Found Exception';
    }
}
