<?php

declare(strict_types=1);

namespace bizley\podium\api\services\member;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\GroupEvent;
use bizley\podium\api\interfaces\GrouperInterface;
use bizley\podium\api\interfaces\GroupRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\repositories\MemberRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class MemberGrouper extends Component implements GrouperInterface
{
    public const EVENT_BEFORE_JOINING = 'podium.group.joining.before';
    public const EVENT_AFTER_JOINING = 'podium.group.joining.after';
    public const EVENT_BEFORE_LEAVING = 'podium.group.leaving.before';
    public const EVENT_AFTER_LEAVING = 'podium.group.leaving.after';

    private ?MemberRepositoryInterface $member = null;

    /**
     * @var string|array|MemberRepositoryInterface
     */
    public $repositoryConfig = MemberRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getMember(): MemberRepositoryInterface
    {
        if (null === $this->member) {
            /** @var MemberRepositoryInterface $member */
            $member = Instance::ensure($this->repositoryConfig, MemberRepositoryInterface::class);
            $this->member = $member;
        }

        return $this->member;
    }

    public function beforeJoin(): bool
    {
        $event = new GroupEvent();
        $this->trigger(self::EVENT_BEFORE_JOINING, $event);

        return $event->canJoin;
    }

    public function join($id, GroupRepositoryInterface $group): PodiumResponse
    {
        if (!$this->beforeJoin()) {
            return PodiumResponse::error();
        }

        try {
            $member = $this->getMember();
            if (!$member->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'member.not.exists')]);
            }
            $groupId = $group->getId();
            if ($member->isMemberOfGroup($groupId)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'group.already.joined')]);
            }
            if (!$member->join($groupId)) {
                return PodiumResponse::error($member->getErrors());
            }

            $this->afterJoin();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while joining group', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterJoin(): void
    {
        $this->trigger(self::EVENT_AFTER_JOINING, new GroupEvent(['model' => $this]));
    }

    public function beforeLeave(): bool
    {
        $event = new GroupEvent();
        $this->trigger(self::EVENT_BEFORE_LEAVING, $event);

        return $event->canLeave;
    }

    public function leave($id, GroupRepositoryInterface $group): PodiumResponse
    {
        if (!$this->beforeLeave()) {
            return PodiumResponse::error();
        }

        try {
            $member = $this->getMember();
            if (!$member->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'member.not.exists')]);
            }
            $groupId = $group->getId();
            if (!$member->isMemberOfGroup($groupId)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'group.not.joined')]);
            }
            if (!$member->leave($groupId)) {
                return PodiumResponse::error($member->getErrors());
            }

            $this->afterJoin();

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
