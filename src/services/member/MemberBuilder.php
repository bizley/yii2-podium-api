<?php

declare(strict_types=1);

namespace bizley\podium\api\services\member;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\BuildEvent;
use bizley\podium\api\interfaces\MemberBuilderInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\repositories\MemberRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class MemberBuilder extends Component implements MemberBuilderInterface
{
    public const EVENT_BEFORE_REGISTERING = 'podium.member.registering.before';
    public const EVENT_AFTER_REGISTERING = 'podium.member.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.member.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.member.editing.after';
    public const EVENT_BEFORE_ACTIVATING = 'podium.member.activating.before';
    public const EVENT_AFTER_ACTIVATING = 'podium.member.activating.after';

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

    public function beforeRegister(): bool
    {
        $event = new BuildEvent();
        $this->trigger(self::EVENT_BEFORE_REGISTERING, $event);

        return $event->canCreate;
    }

    /**
     * Registers a member.
     *
     * @param int|string|array $id
     */
    public function register($id, array $data = []): PodiumResponse
    {
        if (!$this->beforeRegister()) {
            return PodiumResponse::error();
        }

        try {
            $member = $this->getMember();

            if (!$member->register($id, $data)) {
                return PodiumResponse::error($member->getErrors());
            }

            $this->afterRegister($member);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while registering member', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterRegister(MemberRepositoryInterface $member): void
    {
        $this->trigger(self::EVENT_AFTER_REGISTERING, new BuildEvent(['repository' => $member]));
    }

    public function beforeEdit(): bool
    {
        $event = new BuildEvent();
        $this->trigger(self::EVENT_BEFORE_EDITING, $event);

        return $event->canEdit;
    }

    /**
     * Edits the member.
     */
    public function edit(MemberRepositoryInterface $member, array $data = []): PodiumResponse
    {
        if (!$this->beforeEdit()) {
            return PodiumResponse::error();
        }

        try {
            if (!$member->edit($data)) {
                return PodiumResponse::error($member->getErrors());
            }

            $this->afterEdit($member);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while editing member', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterEdit(MemberRepositoryInterface $member): void
    {
        $this->trigger(self::EVENT_AFTER_EDITING, new BuildEvent(['repository' => $member]));
    }

    public function beforeActivate(): bool
    {
        $event = new BuildEvent();
        $this->trigger(self::EVENT_BEFORE_ACTIVATING, $event);

        return $event->canEdit;
    }

    /**
     * Activates the member.
     */
    public function activate(MemberRepositoryInterface $member): PodiumResponse
    {
        if (!$this->beforeActivate()) {
            return PodiumResponse::error();
        }

        try {
            if (!$member->activate()) {
                return PodiumResponse::error($member->getErrors());
            }

            $this->afterActivate($member);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while activating member', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterActivate(MemberRepositoryInterface $member): void
    {
        $this->trigger(self::EVENT_AFTER_ACTIVATING, new BuildEvent(['repository' => $member]));
    }
}
