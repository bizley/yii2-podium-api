<?php

namespace bizley\podium\api\repos;

/**
 * Class RepoNotFoundException
 * @package bizley\podium\api\repos
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
