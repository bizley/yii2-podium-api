<?php

declare(strict_types=1);

namespace bizley\podium\api\models\poll;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\PollModelInterface;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\models\post\Post;
use bizley\podium\api\repos\PollRepo;
use yii\base\NotSupportedException;
use yii\db\ActiveQuery;

/**
 * Class Poll
 * @package bizley\podium\api\models\poll
 *
 * @property ModelInterface $parent
 * @property Post $post
 * @property int $choiceId
 */
class Poll extends PollRepo implements PollModelInterface
{
    use ModelTrait;

    /**
     * @param int $modelId
     * @return ModelInterface|null
     */
    public static function findByPostId(int $modelId): ?ModelInterface
    {
        return static::findOne(['post_id' => $modelId]);
    }

    /**
     * @return ModelInterface
     */
    public function getParent(): ModelInterface
    {
        return $this->post;
    }

    /**
     * @return ActiveQuery
     */
    public function getPost(): ActiveQuery
    {
        return $this->hasOne(Post::class, ['id' => 'post_id']);
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
     */
    public function isArchived(): bool
    {
        return $this->getParent()->isArchived();
    }

    /**
     * @return string
     */
    public function getChoiceId(): string
    {
        return $this->choice_id;
    }
}
