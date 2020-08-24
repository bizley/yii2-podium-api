<?php

declare(strict_types=1);

namespace bizley\podium\api\services\group;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\BuildEvent;
use bizley\podium\api\interfaces\BuilderInterface;
use bizley\podium\api\interfaces\GroupRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
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
        $event = new BuildEvent();
        $this->trigger(self::EVENT_BEFORE_CREATING, $event);

        return $event->canCreate;
    }

    /**
     * Creates new group.
     */
    public function create(array $data = []): PodiumResponse
    {
        if (!$this->beforeCreate()) {
            return PodiumResponse::error();
        }

        try {
            $group = $this->getGroup();

            if (!$group->create($data)) {
                return PodiumResponse::error($group->getErrors());
            }

            $this->afterCreate($group);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while creating group', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterCreate(GroupRepositoryInterface $group): void
    {
        $this->trigger(self::EVENT_AFTER_CREATING, new BuildEvent(['repository' => $group]));
    }

    public function beforeEdit(): bool
    {
        $event = new BuildEvent();
        $this->trigger(self::EVENT_BEFORE_EDITING, $event);

        return $event->canEdit;
    }

    /**
     * Edits the group.
     */
    public function edit(RepositoryInterface $group, array $data = []): PodiumResponse
    {
        if (!$group instanceof GroupRepositoryInterface || !$this->beforeEdit()) {
            return PodiumResponse::error();
        }

        try {
            if (!$group->edit($data)) {
                return PodiumResponse::error($group->getErrors());
            }

            $this->afterEdit($group);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while editing group', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterEdit(GroupRepositoryInterface $group): void
    {
        $this->trigger(self::EVENT_AFTER_EDITING, new BuildEvent(['repository' => $group]));
    }
}
