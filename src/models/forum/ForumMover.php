<?php

declare(strict_types=1);

namespace bizley\podium\api\models\forum;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\MoveEvent;
use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\MoverInterface;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;

/**
 * Class ForumMover
 * @package bizley\podium\api\models\forum
 */
class ForumMover extends Forum implements MoverInterface
{
    public const EVENT_BEFORE_MOVING = 'podium.forum.moving.before';
    public const EVENT_AFTER_MOVING = 'podium.forum.moving.after';

    /**
     * @param ModelInterface $category
     * @throws InsufficientDataException
     */
    public function prepareCategory(ModelInterface $category): void
    {
        $categoryId = $category->getId();
        if ($categoryId === null) {
            throw new InsufficientDataException('Missing category Id for forum mover');
        }
        $this->category_id = $categoryId;
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
    public function prepareForum(ModelInterface $forum): void
    {
        throw new NotSupportedException('Forum can not be moved to a Forum.');
    }

    /**
     * @param ModelInterface $thread
     * @throws NotSupportedException
     */
    public function prepareThread(ModelInterface $thread): void
    {
        throw new NotSupportedException('Forum can not be moved to a Thread.');
    }
}
