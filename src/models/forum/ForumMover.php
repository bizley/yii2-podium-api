<?php

declare(strict_types=1);

namespace bizley\podium\api\models\forum;

use bizley\podium\api\base\PodiumResponse;
use bizley\podium\api\events\MoveEvent;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MovableInterface;
use bizley\podium\api\repos\ForumRepo;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;

/**
 * Class ForumMover
 * @package bizley\podium\api\models\forum
 */
class ForumMover extends ForumRepo implements MovableInterface
{
    public const EVENT_BEFORE_MOVING = 'podium.forum.moving.before';
    public const EVENT_AFTER_MOVING = 'podium.forum.moving.after';

    /**
     * @param int $modelId
     * @return MovableInterface|null
     */
    public static function findById(int $modelId): ?MovableInterface
    {
        return static::findOne(['id' => $modelId]);
    }

    /**
     * @param ModelInterface $category
     */
    public function setCategory(ModelInterface $category): void
    {
        $this->category_id = $category->getId();
    }

    /**
     * @return array
     */
    public function behaviors(): array
    {
        return ['timestamp' => TimestampBehavior::class];
    }

    /**
     * @return bool
     */
    public function beforeMove(): bool
    {
        $event = new MoveEvent();
        $this->trigger(self::EVENT_BEFORE_MOVING, $event);

        return $event->canMove;
    }

    /**
     * @return PodiumResponse
     */
    public function move(): PodiumResponse
    {
        if (!$this->beforeMove()) {
            return PodiumResponse::error();
        }

        if (!$this->save()) {
            Yii::error(['Error while moving forum', $this->errors], 'podium');
            return PodiumResponse::error($this);
        }

        $this->afterMove();

        return PodiumResponse::success();
    }

    public function afterMove(): void
    {
        $this->trigger(self::EVENT_AFTER_MOVING, new MoveEvent(['model' => $this]));
    }

    /**
     * @param ModelInterface $forum
     * @throws NotSupportedException
     */
    public function setForum(ModelInterface $forum): void
    {
        throw new NotSupportedException('Forum can not be moved to a Forum.');
    }

    /**
     * @param ModelInterface $thread
     * @throws NotSupportedException
     */
    public function setThread(ModelInterface $thread): void
    {
        throw new NotSupportedException('Forum can not be moved to a Thread.');
    }
}
