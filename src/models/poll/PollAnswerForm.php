<?php

declare(strict_types=1);

namespace bizley\podium\api\models\poll;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\interfaces\AnswerFormInterface;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\repos\PollAnswerRepo;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;

/**
 * Class PollAnswerForm
 * @package bizley\podium\api\models\poll
 */
class PollAnswerForm extends PollAnswerRepo implements AnswerFormInterface
{
    /**
     * @return array
     */
    public function behaviors(): array
    {
        return ['timestamp' => TimestampBehavior::class];
    }

    /**
     * Loads form data.
     * @param array $data form data
     * @return bool
     * @throws NotSupportedException
     */
    public function loadData(array $data = []): bool
    {
        throw new NotSupportedException('Use PollForm to create poll.');
    }

    /**
     * Creates new model.
     * @return PodiumResponse
     */
    public function create(): PodiumResponse
    {
        if (!$this->save(false)) {
            Yii::error('Error while creating poll answer', 'podium');

            return PodiumResponse::error();
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
        throw new NotSupportedException('Use PollForm to update poll.');
    }

    /**
     * @param int $modelFormId
     * @return ModelFormInterface|null
     * @throws NotSupportedException
     */
    public static function findById(int $modelFormId): ?ModelFormInterface
    {
        throw new NotSupportedException('Use PollForm to update poll.');
    }

    /**
     * @param int $pollId
     */
    public function setPollId(int $pollId): void
    {
        $this->poll_id = $pollId;
    }

    /**
     * @param string $answer
     */
    public function setAnswer(string $answer): void
    {
        $this->answer = $answer;
    }
}
