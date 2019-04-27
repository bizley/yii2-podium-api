<?php

declare(strict_types=1);

namespace bizley\podium\api\models\poll;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\PollModelInterface;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\repos\PollRepo;
use yii\base\InvalidValueException;
use yii\base\NotSupportedException;
use yii\db\ActiveQuery;

/**
 * Class Poll
 * @package bizley\podium\api\models\poll
 *
 * @property ModelInterface $parent
 * @property Thread $thread
 * @property int $choiceId
 */
class Poll extends PollRepo implements PollModelInterface
{
    use ModelTrait;

    /**
     * @param int $modelId
     * @return PollModelInterface|null
     */
    public static function getByThreadId(int $modelId): ?PollModelInterface
    {
        return static::findOne(['thread_id' => $modelId]);
    }

    /**
     * @return ModelInterface|null
     */
    public function getParent(): ?ModelInterface
    {
        return $this->thread;
    }

    /**
     * @return ActiveQuery
     */
    public function getThread(): ActiveQuery
    {
        return $this->hasOne(Thread::class, ['id' => 'thread_id']);
    }

    /**
     * @return int
     * @throws NotSupportedException
     */
    public function getPostsCount(): int
    {
        throw new NotSupportedException('Poll has got no posts.');
    }

    /**
     * @return bool
     * @throws InvalidValueException
     */
    public function isArchived(): bool
    {
        $post = $this->getParent();
        if ($post === null) {
            throw new InvalidValueException('Floating poll detected!');
        }

        return $post->isArchived();
    }

    /**
     * @return string
     */
    public function getChoiceId(): string
    {
        return $this->choice_id;
    }
}
