<?php

declare(strict_types=1);

namespace bizley\podium\api\models\poll;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\PollAnswerModelInterface;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\repos\PollAnswerRepo;
use yii\base\NotSupportedException;
use yii\db\ActiveQuery;

/**
 * Class PollAnswer
 * @package bizley\podium\api\models\poll
 *
 * @property ModelInterface $parent
 * @property Poll $poll
 * @property int $pollId
 */
class PollAnswer extends PollAnswerRepo implements PollAnswerModelInterface
{
    use ModelTrait;

    /**
     * @return ModelInterface
     */
    public function getParent(): ModelInterface
    {
        return $this->poll;
    }

    /**
     * @return ActiveQuery
     */
    public function getPoll(): ActiveQuery
    {
        return $this->hasOne(Poll::class, ['id' => 'poll_id']);
    }

    /**
     * @return int
     * @throws NotSupportedException
     */
    public function getPostsCount(): int
    {
        throw new NotSupportedException('Poll Answer has got no posts.');
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->getParent()->getParent()->isArchived();
    }

    /**
     * @return int
     */
    public function getPollId(): int
    {
        return $this->poll_id;
    }
}
