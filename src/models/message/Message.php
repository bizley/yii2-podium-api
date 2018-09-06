<?php

declare(strict_types=1);

namespace bizley\podium\api\models\message;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\repos\MessageRepo;
use yii\base\NotSupportedException;
use yii\db\ActiveQuery;

/**
 * Class Message
 * @package bizley\podium\api\models\message
 *
 * @property ModelInterface $parent
 * @property Message $repliedMessage
 */
class Message extends MessageRepo implements ModelInterface
{
    use ModelTrait;

    /**
     * @return ModelInterface|null
     */
    public function getParent(): ?ModelInterface
    {
        return $this->repliedMessage;
    }

    /**
     * @return ActiveQuery
     */
    public function getRepliedMessage(): ActiveQuery
    {
        return $this->hasOne(static::class, ['id' => 'reply_to_id']);
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
     * @throws NotSupportedException
     */
    public function isArchived(): bool
    {
        throw new NotSupportedException('Message itself can not be archived.');
    }
}
