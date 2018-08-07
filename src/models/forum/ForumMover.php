<?php

declare(strict_types=1);

namespace bizley\podium\api\models\forum;

use bizley\podium\api\events\MoveEvent;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MovableInterface;
use bizley\podium\api\repos\ForumRepo;
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
        return [
            'timestamp' => TimestampBehavior::class,
        ];
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
     * @return bool
     */
    public function move(): bool
    {
        if (!$this->beforeMove()) {
            return false;
        }

//        if (!$this->save(false)) {
//            Yii::error(['forum.create', $this->errors], 'podium');
//            return false;
//        }
        $this->afterMove();
        return true;
    }

    public function afterMove(): void
    {
        $this->trigger(self::EVENT_AFTER_MOVING, new MoveEvent([
            'model' => $this
        ]));
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
