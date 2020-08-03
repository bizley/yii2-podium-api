<?php

declare(strict_types=1);

namespace bizley\podium\api\models\poll;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\repos\PollVoteRepo;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;

/**
 * Class PollVoteForm
 * @package bizley\podium\api\models\poll
 */
class PollVoteForm extends PollVoteRepo implements ModelFormInterface
{
    /**
     * @return array
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * Loads form data.
     * @param array $data form data
     * @return bool
     * @throws NotSupportedException
     */
    public function loadData(array $data = []): bool
    {
        throw new NotSupportedException('Use Voting to vote in poll.');
    }

    /**
     * Creates new model.
     * @return PodiumResponse
     */
    public function create(): PodiumResponse
    {
        if (!$this->save(false)) {
            Yii::error('Error while creating poll', 'podium');
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
        throw new NotSupportedException('Votes can not be edited.');
    }

    /**
     * @param int $modelFormId
     * @return ModelInterface|null
     * @throws NotSupportedException
     */
    public static function findById(int $modelFormId): ?ModelInterface
    {
        throw new NotSupportedException('Use PostPollForm to find poll.');
    }
}
