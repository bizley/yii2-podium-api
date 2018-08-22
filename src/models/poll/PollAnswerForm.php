<?php

declare(strict_types=1);

namespace bizley\podium\api\models\poll;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\repos\PollAnswerRepo;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;

/**
 * Class PollAnswerForm
 * @package bizley\podium\api\models\poll
 */
class PollAnswerForm extends PollAnswerRepo implements ModelFormInterface
{
    /**
     * @return array
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => TimestampBehavior::class,
        ];
    }

    /**
     * Loads form data.
     * @param array|null $data form data
     * @return bool
     * @throws NotSupportedException
     */
    public function loadData(?array $data = null): bool
    {
        throw new NotSupportedException('Use PostPollForm to create poll.');
    }

    /**
     * Creates new model.
     * @return PodiumResponse
     */
    public function create(): PodiumResponse
    {
        if (!$this->save(false)) {
            Yii::error(['Error while creating poll answer', $this->errors], 'podium');
            return PodiumResponse::error($this);
        }
        return PodiumResponse::success();
    }

    /**
     * Updates model.
     * @return PodiumResponse
     * @throws NotSupportedException
     */
    public function edit(): PodiumResponse
    {
        throw new NotSupportedException('Use PostPollForm to update poll.');
    }
}
