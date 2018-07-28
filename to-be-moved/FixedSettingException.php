<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use yii\base\Exception;

/**
 * Class FixedSettingException
 * @package bizley\podium\api\base
 */
class FixedSettingException extends Exception
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Fixed Setting Exception';
    }
}
