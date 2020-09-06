<?php

declare(strict_types=1);

namespace bizley\podium\api\services\group;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\GroupEvent;
use bizley\podium\api\interfaces\GroupMemberRepositoryInterface;
use bizley\podium\api\interfaces\GroupRepositoryInterface;
use bizley\podium\api\interfaces\KeeperInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\repositories\GroupMemberRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class GroupKeeper extends Component implements KeeperInterface
{
    public const EVENT_BEFORE_JOINING = 'podium.group.joining.before';
    public const EVENT_AFTER_JOINING = 'podium.group.joining.after';
    public const EVENT_BEFORE_LEAVING = 'podium.group.leaving.before';
    public const EVENT_AFTER_LEAVING = 'podium.group.leaving.after';

    private ?GroupMemberRepositoryInterface $groupMember = null;

    /**
     * @var string|array|GroupMemberRepositoryInterface
     */
    public $repositoryConfig = GroupMemberRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getGroupMember(): GroupMemberRepositoryInterface
    {
        if (null === $this->groupMember) {
            /** @var GroupMemberRepositoryInterface $groupMember */
            $groupMember = Instance::ensure($this->repositoryConfig, GroupMemberRepositoryInterface::class);
            $this->groupMember = $groupMember;
        }

        return $this->groupMember;
    }

    public function beforeJoin(): bool
    {
        $event = new GroupEvent();
        $this->trigger(self::EVENT_BEFORE_JOINING, $event);

        return $event->canJoin;
    }

    public function join(GroupRepositoryInterface $group, MemberRepositoryInterface $member): PodiumResponse
    {
        if (!$this->beforeJoin()) {
            return PodiumResponse::error();
        }

        try {
            $groupMember = $this->getGroupMember();

            if ($groupMember->fetchOne($group, $member)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'group.already.joined')]);
            }

            if (!$groupMember->create($group, $member)) {
                return PodiumResponse::error($groupMember->getErrors());
            }

            $this->afterJoin($groupMember);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while joining group', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterJoin(GroupMemberRepositoryInterface $groupMember): void
    {
        $this->trigger(self::EVENT_AFTER_JOINING, new GroupEvent(['repository' => $groupMember]));
    }

    public function beforeLeave(): bool
    {
        $event = new GroupEvent();
        $this->trigger(self::EVENT_BEFORE_LEAVING, $event);

        return $event->canLeave;
    }

    public function leave(GroupRepositoryInterface $group, MemberRepositoryInterface $member): PodiumResponse
    {
        if (!$this->beforeLeave()) {
            return PodiumResponse::error();
        }

        try {
            $groupMember = $this->getGroupMember();

            if (!$groupMember->fetchOne($group, $member)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'group.not.joined')]);
            }

            if (!$groupMember->delete()) {
                return PodiumResponse::error();
            }

            $this->afterLeave();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while leaving group', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterLeave(): void
    {
        $this->trigger(self::EVENT_AFTER_LEAVING);
    }
}
