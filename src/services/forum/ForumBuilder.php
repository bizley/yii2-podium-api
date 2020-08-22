<?php

declare(strict_types=1);

namespace bizley\podium\api\services\forum;

use bizley\podium\api\components\PodiumResponse;
use bizley\podium\api\events\BuildEvent;
use bizley\podium\api\interfaces\CategorisedBuilderInterface;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\repositories\ForumRepository;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

final class ForumBuilder extends Component implements CategorisedBuilderInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.forum.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.forum.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.forum.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.forum.editing.after';

    private ?ForumRepositoryInterface $forum = null;

    /**
     * @var string|array|ForumRepositoryInterface
     */
    public $repositoryConfig = ForumRepository::class;

    /**
     * @throws InvalidConfigException
     */
    private function getForum(): ForumRepositoryInterface
    {
        if (null === $this->forum) {
            /** @var ForumRepositoryInterface $forum */
            $forum = Instance::ensure($this->repositoryConfig, ForumRepositoryInterface::class);
            $this->forum = $forum;
        }

        return $this->forum;
    }

    public function beforeCreate(): bool
    {
        $event = new BuildEvent();
        $this->trigger(self::EVENT_BEFORE_CREATING, $event);

        return $event->canCreate;
    }

    /**
     * Creates new forum.
     */
    public function create(
        MemberRepositoryInterface $author,
        RepositoryInterface $category,
        array $data = []
    ): PodiumResponse {
        if (!$category instanceof CategoryRepositoryInterface || !$this->beforeCreate()) {
            return PodiumResponse::error();
        }

        try {
            $forum = $this->getForum();

            if (!$forum->create($author->getId(), $category->getId(), $data)) {
                return PodiumResponse::error($forum->getErrors());
            }

            $this->afterCreate($forum);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while creating forum', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterCreate(ForumRepositoryInterface $forum): void
    {
        $this->trigger(self::EVENT_AFTER_CREATING, new BuildEvent(['repository' => $forum]));
    }

    public function beforeEdit(): bool
    {
        $event = new BuildEvent();
        $this->trigger(self::EVENT_BEFORE_EDITING, $event);

        return $event->canEdit;
    }

    /**
     * Edits the forum.
     */
    public function edit(RepositoryInterface $forum, array $data = []): PodiumResponse
    {
        if (!$forum instanceof ForumRepositoryInterface || !$this->beforeEdit()) {
            return PodiumResponse::error();
        }

        try {
            if (!$forum->edit($data)) {
                return PodiumResponse::error($forum->getErrors());
            }

            $this->afterEdit($forum);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while editing forum', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error();
        }
    }

    public function afterEdit(ForumRepositoryInterface $forum): void
    {
        $this->trigger(self::EVENT_AFTER_EDITING, new BuildEvent(['repository' => $forum]));
    }
}
