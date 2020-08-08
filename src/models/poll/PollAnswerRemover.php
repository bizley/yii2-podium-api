<?php

declare(strict_types=1);

namespace bizley\podium\api\models\poll;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\interfaces\RemoverInterface;
use Throwable;
use Yii;

/**
 * Class PollAnswerRemover
 * @package bizley\podium\api\models\poll
 */
class PollAnswerRemover extends PollAnswer implements RemoverInterface
{
    /**
     * @return PodiumResponse
     */
    public function remove(): PodiumResponse
    {
        try {
            if ($this->delete() === false) {
                Yii::error('Error while deleting poll answer', 'podium');

                return PodiumResponse::error();
            }

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(
                ['Exception while removing poll answer', $exc->getMessage(), $exc->getTraceAsString()],
                'podium'
            );

            return PodiumResponse::error();
        }
    }
}
