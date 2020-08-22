<?php

declare(strict_types=1);

namespace bizley\podium\api\services\poll;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\BuildEvent;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\PollBuilderInterface;
use bizley\podium\api\interfaces\PollRepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\repositories\PollRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\Transaction;
use yii\di\Instance;

final class PollBuilder extends Component implements PollBuilderInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.poll.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.poll.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.poll.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.poll.editing.after';

    private ?PollRepositoryInterface $poll = null;

    /**
     * @var string|array|PollRepositoryInterface
     */
    public $repositoryConfig = PollRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getPoll(): PollRepositoryInterface
    {
        if (null === $this->poll) {
            /** @var PollRepositoryInterface $post */
            $post = Instance::ensure($this->repositoryConfig, PollRepositoryInterface::class);
            $this->poll = $post;
        }

        return $this->poll;
    }

    public function beforeCreate(): bool
    {
        $event = new BuildEvent();
        $this->trigger(self::EVENT_BEFORE_CREATING, $event);

        return $event->canCreate;
    }

    /**
     * Creates new poll.
     */
    public function create(
        array $data,
        array $answers,
        MemberRepositoryInterface $author,
        ThreadRepositoryInterface $thread
    ): PodiumResponse {
        if (!$this->beforeCreate()) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $poll = $this->getPoll();
            if (!$poll->create($data, $answers, $author->getId(), $thread->getId())) {
                return PodiumResponse::error($poll->getErrors());
            }

            $this->afterCreate();
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while creating poll', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterCreate(): void
    {
        $this->trigger(self::EVENT_AFTER_CREATING, new BuildEvent(['model' => $this]));
    }

    public function beforeEdit(): bool
    {
        $event = new BuildEvent();
        $this->trigger(self::EVENT_BEFORE_EDITING, $event);

        return $event->canEdit;
    }

    /**
     * Edits the thread.
     */
    public function edit($id, array $data, array $answers): PodiumResponse
    {
        if (!$this->beforeEdit()) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = $this->getPoll();
            if (!$post->fetchOne($id)) {
                return PodiumResponse::error(['api' => Yii::t('podium.error', 'poll.not.exists')]);
            }

            if (!$post->edit($data, $answers)) {
                return PodiumResponse::error($post->getErrors());
            }

            $this->afterEdit();
            $transaction->commit();

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while editing poll', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterEdit(): void
    {
        $this->trigger(self::EVENT_AFTER_EDITING, new BuildEvent(['model' => $this]));
    }
}
