<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface ThreadInterface
{
    /**
     * Creates thread.
     */
    public function create(
        MemberRepositoryInterface $author,
        ForumRepositoryInterface $forum,
        array $data = []
    ): PodiumResponse;

    /**
     * Updates thread.
     */
    public function edit(ThreadRepositoryInterface $thread, array $data = []): PodiumResponse;

    public function remove(ThreadRepositoryInterface $thread): PodiumResponse;

    /**
     * Moves thread to different forum.
     */
    public function move(ThreadRepositoryInterface $thread, ForumRepositoryInterface $forum): PodiumResponse;

    public function pin(ThreadRepositoryInterface $thread): PodiumResponse;

    public function unpin(ThreadRepositoryInterface $thread): PodiumResponse;

    public function lock(ThreadRepositoryInterface $thread): PodiumResponse;

    public function unlock(ThreadRepositoryInterface $thread): PodiumResponse;

    public function archive(ThreadRepositoryInterface $thread): PodiumResponse;

    public function revive(ThreadRepositoryInterface $thread): PodiumResponse;

    public function subscribe(ThreadRepositoryInterface $thread, MemberRepositoryInterface $member): PodiumResponse;

    public function unsubscribe(ThreadRepositoryInterface $thread, MemberRepositoryInterface $member): PodiumResponse;

    public function mark(PostRepositoryInterface $post, MemberRepositoryInterface $member): PodiumResponse;
}
