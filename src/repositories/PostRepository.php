<?php

declare(strict_types=1);

namespace bizley\podium\api\repositories;

use bizley\podium\api\ars\PostActiveRecord;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use LogicException;

final class PostRepository implements PostRepositoryInterface
{
    use ActiveRecordRepositoryTrait;

    public string $activeRecordClass = PostActiveRecord::class;

    private ?PostActiveRecord $model = null;

    public function getActiveRecordClass(): string
    {
        return $this->activeRecordClass;
    }

    public function getModel(): PostActiveRecord
    {
        if (null === $this->model) {
            throw new LogicException('You need to call fetchOne() or setModel() first!');
        }

        return $this->model;
    }

    public function setModel(?PostActiveRecord $activeRecord): void
    {
        $this->model = $activeRecord;
    }

    public function getId(): int
    {
        return $this->getModel()->id;
    }

    public function getParent(): RepositoryInterface
    {
        $threadRepository = $this->getModel()->thread;
        $parent = new ThreadRepository();
        $parent->setModel($threadRepository);

        return $parent;
    }

    public function isArchived(): bool
    {
        return $this->getModel()->archived;
    }

    public function getCreatedAt(): int
    {
        return $this->getModel()->created_at;
    }

    public function create(array $data, $authorId, $threadId, $forumId, $categoryId): bool
    {
        /** @var PostActiveRecord $post */
        $post = new $this->activeRecordClass();
        if (!$post->load($data, '')) {
            return false;
        }

        $post->author_id = $authorId;
        $post->thread_id = $threadId;
        $post->forum_id = $forumId;
        $post->category_id = $categoryId;

        if (!$post->validate()) {
            $this->errors = $post->errors;
        }

        return $post->save(false);
    }

    public function move($threadId, $forumId, $categoryId): bool
    {
        $post = $this->getModel();
        $post->thread_id = $threadId;
        $post->forum_id = $forumId;
        $post->category_id = $categoryId;
        if (!$post->validate()) {
            $this->errors = $post->errors;
        }

        return $post->save(false);
    }

    public function archive(): bool
    {
        $post = $this->getModel();
        $post->archived = true;
        if (!$post->validate()) {
            $this->errors = $post->errors;
        }

        return $post->save(false);
    }

    public function revive(): bool
    {
        $post = $this->getModel();
        $post->archived = false;
        if (!$post->validate()) {
            $this->errors = $post->errors;
        }

        return $post->save(false);
    }

    public function updateCounters(int $likes, int $dislikes): bool
    {
        return $this->getModel()->updateCounters(
            [
                'likes' => $likes,
                'dislikes' => $dislikes,
            ]
        );
    }
}
