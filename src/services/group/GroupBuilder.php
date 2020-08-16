<?php

declare(strict_types=1);

namespace bizley\podium\api\services\group;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\ModelEvent;
use bizley\podium\api\interfaces\BuilderInterface;
use bizley\podium\api\interfaces\GroupRepositoryInterface;
use bizley\podium\api\repositories\GroupRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class GroupBuilder extends Component implements BuilderInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.group.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.group.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.group.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.group.editing.after';

    private ?GroupRepositoryInterface $group = null;

    /**
     * @var string|array|GroupRepositoryInterface
     */
    public $repositoryConfig = GroupRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getGroup(): GroupRepositoryInterface
    {
        if (null === $this->group) {
            /** @var GroupRepositoryInterface $group */
            $group = Instance::ensure($this->repositoryConfig, GroupRepositoryInterface::class);
            $this->group = $group;
        }

        return $this->group;
    }

    public function beforeCreate(): bool
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_CREATING, $event);

        return $event->canCreate;
    }

    /**
     * Creates new group.
     */
    public function create(array $data): PodiumResponse
    {
        if (!$this->beforeCreate()) {
            return PodiumResponse::error();
        }

        try {
            $group = $this->getGroup();

            if (!$group->create($data)) {
                return PodiumResponse::error($group->getErrors());
            }

            $this->afterCreate();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while creating group', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

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
     * Edits the group.
     */
    public function edit($id, array $data): PodiumResponse
    {
        if (!$this->beforeEdit()) {
            return PodiumResponse::error();
        }

        try {
            $group = $this->getGroup();
            if (!$group->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'group.not.exists')]);
            }

            if (!$group->edit($data)) {
                return PodiumResponse::error($group->getErrors());
            }

            $this->afterEdit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while editing group', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterEdit(): void
    {
        $this->trigger(self::EVENT_AFTER_EDITING, new ModelEvent(['model' => $this]));
    }
}
