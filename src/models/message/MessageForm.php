<?php

declare(strict_types=1);

namespace bizley\podium\api\models\message;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\enums\MessageStatus;
use bizley\podium\api\interfaces\MessageFormInterface;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Class MessageForm
 * @package bizley\podium\api\models\message
 */
class MessageForm extends MessageParticipant implements MessageFormInterface
{
    /**
     * @return array
     */
    public function behaviors(): array
    {
        return ['timestamp' => TimestampBehavior::class];
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
     * @param int $senderId
     * @param int $replyId
     * @return MessageFormInterface|null
     */
    public function findMessageToReply(int $senderId, int $replyId): ?MessageFormInterface
    {
        return static::findOne([
            'member_id' => $senderId,
            'message_id' => $replyId,
        ]);
    }

    /**
     * @param int $senderId
     */
    public function setSenderId(int $senderId): void
    {
        $this->member_id = $senderId;
    }

    /**
     * @param int $messageId
     */
    public function setMessageId(int $messageId): void
    {
        $this->message_id = $messageId;
    }

    /**
     * @param string $statusId
     */
    public function setStatusId(string $statusId): void
    {
        $this->status_id = $statusId;
    }

    /**
     * @param string $sideId
     */
    public function setSideId(string $sideId): void
    {
        $this->side_id = $sideId;
    }
}
