<?php

declare(strict_types=1);

namespace bizley\podium\api\models\poll;

use bizley\podium\api\interfaces\RemovableInterface;
use bizley\podium\api\repos\PollAnswerRepo;
use Yii;

/**
 * Class PollAnswerRemover
 * @package bizley\podium\api\models\poll
 */
class PollAnswerRemover extends PollAnswerRepo implements RemovableInterface
{
    /**
     * @return bool
     */
    public function remove(): bool
    {
        try {
            if ($this->delete() === false) {
                Yii::error('Error while deleting poll answer', 'podium');
                return false;
            }
            return true;

        } catch (\Throwable $exc) {
            Yii::error(['Exception while removing poll answer', $exc->getMessage(), $exc->getTraceAsString()], 'podium');
        }
        return false;
    }
}
