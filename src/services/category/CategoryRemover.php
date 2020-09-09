<?php

declare(strict_types=1);

namespace bizley\podium\api\services\category;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;

final class CategoryRemover extends Component implements RemoverInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.category.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.category.removing.after';

    /**
     * Calls before removing the category.
     */
    public function beforeRemove(): bool
    {
        $event = new RemoveEvent();
        $this->trigger(self::EVENT_BEFORE_REMOVING, $event);

        return $event->canRemove;
    }

    /**
     * Removes the category.
     */
    public function remove(RepositoryInterface $category): PodiumResponse
    {
        if (!$category instanceof CategoryRepositoryInterface || !$this->beforeRemove()) {
            return PodiumResponse::error();
        }

        try {
            if (!$category->isArchived()) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'category.must.be.archived')]);
            }

            if (!$category->delete()) {
                return PodiumResponse::error();
            }

            $this->afterRemove();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while deleting category', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after removing the category.
     */
    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
