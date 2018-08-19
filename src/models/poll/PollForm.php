<?php

declare(strict_types=1);

namespace bizley\podium\api\models\poll;

use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\repos\PollRepo;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;

/**
 * Class PollForm
 * @package bizley\podium\api\models\poll
 */
class PollForm extends PollRepo implements ModelFormInterface
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
     */
    public function edit(): bool
    {
        // TODO: Implement edit() method.
    }
}
