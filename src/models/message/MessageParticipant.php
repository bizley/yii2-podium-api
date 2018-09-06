<?php

declare(strict_types=1);

namespace bizley\podium\api\models\message;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MessageParticipantModelInterface;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\repos\MessageParticipantRepo;
use yii\base\NotSupportedException;
use yii\db\ActiveQuery;

/**
 * Class Message
 * @package bizley\podium\api\models\message
 *
 * @property ModelInterface $parent
 * @property Message $message
 * @property int $memberId
 * @property string $sideId
 */
class MessageParticipant extends MessageParticipantRepo implements MessageParticipantModelInterface
{
    use ModelTrait;

    /**
     * @return ModelInterface|null
     */
    public function getParent(): ?ModelInterface
    {
        return $this->message;
    }

    /**
     * @return ActiveQuery
     */
    public function getMessage(): ActiveQuery
    {
        return $this->hasOne(Message::class, ['id' => 'message_id']);
    }

    /**
     * @return int
     * @throws NotSupportedException
     */
    public function getPostsCount(): int
    {
        throw new NotSupportedException('Post has got no posts.');
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return (bool) $this->archived;
    }

    /**
     * @return int
     */
    public function getMemberId(): int
    {
        return $this->member_id;
    }

    /**
     * @return string
     */
    public function getSideId(): string
    {
        return $this->side_id;
    }

    /**
     * @return int
     * @throws NotSupportedException
     */
    public function getId(): int
    {
        throw new NotSupportedException('Message Participant has got no ID.');
    }
}
