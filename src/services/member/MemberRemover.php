<?php

declare(strict_types=1);

namespace bizley\podium\api\services\member;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\repositories\MemberRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class MemberRemover extends Component implements RemoverInterface
{
    public const EVENT_BEFORE_REMOVING = 'podium.forum.removing.before';
    public const EVENT_AFTER_REMOVING = 'podium.forum.removing.after';

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
            /** @var MemberRepositoryInterface $acquaintance */
            $acquaintance = Instance::ensure($this->repositoryConfig, MemberRepositoryInterface::class);
            $this->member = $acquaintance;
        }

        return $this->member;
    }

    public function beforeRemove(): bool
    {
        $event = new RemoveEvent();
        $this->trigger(self::EVENT_BEFORE_REMOVING, $event);

        return $event->canRemove;
    }

    public function remove($id): PodiumResponse
    {
        if (!$this->beforeRemove()) {
            return PodiumResponse::error();
        }

        try {
            $member = $this->getMember();
            if (!$member->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'member.not.exists')]);
            }
            if (!$member->delete()) {
                return PodiumResponse::error();
            }

            $this->afterRemove();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while removing member', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterRemove(): void
    {
        $this->trigger(self::EVENT_AFTER_REMOVING);
    }
}
