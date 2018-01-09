<?php

namespace bizley\podium\api\components;

/**
 * Class PermissionRequiredException
 * @package bizley\podium\api\repositories
 */
class PermissionRequiredException extends \yii\base\Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Required Permission Not Granted';
    }
}