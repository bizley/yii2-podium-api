<?php

declare(strict_types=1);

namespace bizley\podium\api\services\category;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\interfaces\CategoryBuilderInterface;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\repositories\CategoryRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class CategoryBuilder extends Component implements CategoryBuilderInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.category.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.category.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.category.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.category.editing.after';

    private ?CategoryRepositoryInterface $category = null;

    /**
     * @var string|array|CategoryRepositoryInterface
     */
    public $repositoryConfig = CategoryRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getCategory(): CategoryRepositoryInterface
    {
        if (null === $this->category) {
            /** @var CategoryRepositoryInterface $category */
            $category = Instance::ensure($this->repositoryConfig, CategoryRepositoryInterface::class);
            $this->category = $category;
        }

        return $this->category;
    }

    public function beforeCreate(): bool
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_CREATING, $event);

        return $event->canCreate;
    }

    /**
     * Creates new category.
     */
    public function create(array $data, MemberRepositoryInterface $author): PodiumResponse
    {
        if (!$this->beforeCreate()) {
            return PodiumResponse::error();
        }

        try {
            $category = $this->getCategory();

            if (!$category->create($data, $author->getId())) {
                return PodiumResponse::error($category->getErrors());
            }

            $this->afterCreate();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while creating category', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterCreate(): void
    {
        $this->trigger(self::EVENT_AFTER_CREATING, new ModelEvent(['model' => $this]));
    }

    public function beforeEdit(): bool
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_EDITING, $event);

        return $event->canEdit;
    }

    /**
     * Edits the category.
     */
    public function edit($id, array $data): PodiumResponse
    {
        if (!$this->beforeEdit()) {
            return PodiumResponse::error();
        }

        try {
            $category = $this->getCategory();
            if (!$category->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'category.not.exists')]);
            }

            if (!$category->edit($data)) {
                return PodiumResponse::error($category->getErrors());
            }

            $this->afterEdit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while editing category', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterEdit(): void
    {
        $this->trigger(self::EVENT_AFTER_EDITING, new ModelEvent(['model' => $this]));
    }
}
