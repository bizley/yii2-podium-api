<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\ThreadActiveRecord;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use DomainException;
use LogicException;

use function is_int;

final class ThreadRepository implements ThreadRepositoryInterface
{
    use ActiveRecordRepositoryTrait;

    public string $activeRecordClass = ThreadActiveRecord::class;

    private ?ThreadActiveRecord $model = null;

    public function getActiveRecordClass(): string
    {
        return $this->activeRecordClass;
    }

    public function getModel(): ThreadActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?ThreadActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function getId(): int
    {
        return $this->getModel()->id;
    }

    public function getParent(): RepositoryInterface
    {
        $forumRepository = $this->getModel()->forum;
        $parent = new ForumRepository();
        $parent->setModel($forumRepository);

        return $parent;
    }

    public function isArchived(): bool
    {
        return $this->getModel()->archived;
    }

    public function getPostsCount(): int
    {
        return $this->getModel()->posts_count;
    }

    public function create(MemberRepositoryInterface $author, ForumRepositoryInterface $forum, array $data = []): bool
    {
        $authorId = $author->getId();
        if (!is_int($authorId)) {
            throw new DomainException('Invalid author ID!');
        }
        $forumId = $forum->getId();
        if (!is_int($forumId)) {
            throw new DomainException('Invalid forum ID!');
        }

        /** @var ThreadActiveRecord $thread */
        $thread = new $this->activeRecordClass();
        if (!$thread->load($data, '')) {
            return false;
        }

        $thread->author_id = $authorId;
        $thread->forum_id = $forumId;

        if (!$thread->save()) {
            $this->errors = $thread->errors;

            return false;
        }

        $this->setModel($thread);

        return true;
    }

    public function pin(): bool
    {
        $thread = $this->getModel();
        $thread->pinned = true;
        if (!$thread->validate()) {
            $this->errors = $thread->errors;

            return false;
        }

        return $thread->save(false);
    }

    public function unpin(): bool
    {
        $thread = $this->getModel();
        $thread->pinned = false;
        if (!$thread->validate()) {
            $this->errors = $thread->errors;

            return false;
        }

        return $thread->save(false);
    }

    public function move(ForumRepositoryInterface $forum): bool
    {
        $forumId = $forum->getId();
        if (!is_int($forumId)) {
            throw new DomainException('Invalid forum ID!');
        }

        $thread = $this->getModel();

        $thread->forum_id = $forumId;

        if (!$thread->validate()) {
            $this->errors = $thread->errors;

            return false;
        }

        return $thread->save(false);
    }

    public function lock(): bool
    {
        $thread = $this->getModel();
        $thread->locked = true;
        if (!$thread->validate()) {
            $this->errors = $thread->errors;

            return false;
        }

        return $thread->save(false);
    }

    public function unlock(): bool
    {
        $thread = $this->getModel();
        $thread->locked = false;
        if (!$thread->validate()) {
            $this->errors = $thread->errors;

            return false;
        }

        return $thread->save(false);
    }

    public function archive(): bool
    {
        $thread = $this->getModel();
        $thread->archived = true;
        if (!$thread->validate()) {
            $this->errors = $thread->errors;

            return false;
        }

        return $thread->save(false);
    }

    public function revive(): bool
    {
        $thread = $this->getModel();
        $thread->archived = false;
        if (!$thread->validate()) {
            $this->errors = $thread->errors;

            return false;
        }

        return $thread->save(false);
    }

    public function updateCounters(int $posts): bool
    {
        return $this->getModel()->updateCounters(['posts_count' => $posts]);
    }

    public function hasPoll(): bool
    {
        return $this->getModel()->getPoll()->exists();
    }
}
