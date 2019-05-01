<?php

declare(strict_types=1);

namespace bizley\podium\api\models\message;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\enums\MessageStatus;
use bizley\podium\api\interfaces\ModelFormInterface;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;

/**
 * Class PollAnswerForm
 * @package bizley\podium\api\models\message
 */
class MessageForm extends MessageParticipant implements ModelFormInterface
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
     * @param array|null $data form data
     * @return bool
     * @throws NotSupportedException
     */
    public function loadData(?array $data = null): bool
    {
        throw new NotSupportedException('Use MessageSender to create message participant copy.');
    }

    /**
     * Creates new model.
     * @return PodiumResponse
     */
    public function create(): PodiumResponse
    {
        if (!$this->save(false)) {
            Yii::error('Error while creating message participant copy', 'podium');
            return PodiumResponse::error();
        }

        return PodiumResponse::success();
    }

    /**
     * @return PodiumResponse
     */
    public function markRead(): PodiumResponse
    {
        $this->status_id = MessageStatus::READ;

        if (!$this->save(false)) {
            Yii::error('Error while marking message participant copy as read', 'podium');
            return PodiumResponse::error();
        }

        return PodiumResponse::success();
    }

    /**
     * @return PodiumResponse
     */
    public function markReplied(): PodiumResponse
    {
        $this->status_id = MessageStatus::REPLIED;

        if (!$this->save(false)) {
            Yii::error(['Error while marking message participant copy as replied', $this->errors], 'podium');
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
        throw new NotSupportedException('Message participant copy can not be updated.');
    }
}
