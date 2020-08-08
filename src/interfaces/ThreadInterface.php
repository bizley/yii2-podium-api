<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface ThreadInterface
{
    /**
     * Creates thread.
     */
    public function create(array $data, MembershipInterface $author, ForumRepositoryInterface $forum): PodiumResponse;

    /**
     * Updates thread.
     */
    public function edit(array $data): PodiumResponse;

    public function remove(int $id): PodiumResponse;

    /**
     * Moves thread to different forum.
     */
    public function move(int $id, ForumRepositoryInterface $forum): PodiumResponse;

    public function pin(int $id): PodiumResponse;

    public function unpin(int $id): PodiumResponse;

    public function lock(int $id): PodiumResponse;

    public function unlock(int $id): PodiumResponse;

    public function archive(int $id): PodiumResponse;

    public function revive(int $id): PodiumResponse;

    public function subscribe(MembershipInterface $member, ThreadRepositoryInterface $thread): PodiumResponse;

    public function unsubscribe(MembershipInterface $member, ThreadRepositoryInterface $thread): PodiumResponse;

    public function mark(MembershipInterface $member, PostRepositoryInterface $post): PodiumResponse;
}
