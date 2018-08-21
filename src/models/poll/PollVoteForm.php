<?php

declare(strict_types=1);

namespace bizley\podium\api\models\poll;

use bizley\podium\api\interfaces\ModelFormInterface;
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
     * @param array|null $data form data
     * @return bool
     * @throws NotSupportedException
     */
    public function loadData(?array $data = null): bool
    {
        throw new NotSupportedException('Use Voting to vote in poll.');
    }

    /**
     * Creates new model.
     * @return bool
     */
    public function create(): bool
    {
        if (!$this->save(false)) {
            Yii::error(['Error while creating poll', $this->errors], 'podium');
            return false;
        }
        return true;
    }

    /**
     * Updates model.
     * @return bool
     * @throws NotSupportedException
     */
    public function edit(): bool
    {
        throw new NotSupportedException('Votes can not be edited.');
    }
}
