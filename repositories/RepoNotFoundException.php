<?php

namespace bizley\podium\api\repositories;

/**
 * Class RepoNotFoundException
 * @package bizley\podium\api\repositories
 */
class RepoNotFoundException extends \yii\base\Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Repository Data Not Found';
    }
}