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
        array $data,
        MemberRepositoryInterface $author,
        ForumRepositoryInterface $forum
    ): PodiumResponse;

    /**
     * Updates thread.
     */
    public function edit($id, array $data): PodiumResponse;

    public function remove($id): PodiumResponse;

    /**
     * Moves thread to different forum.
     */
    public function move($id, ForumRepositoryInterface $forum): PodiumResponse;

    public function pin($id): PodiumResponse;

    public function unpin($id): PodiumResponse;

    public function lock($id): PodiumResponse;

    public function unlock($id): PodiumResponse;

    public function archive($id): PodiumResponse;

    public function revive($id): PodiumResponse;

    public function subscribe(MemberRepositoryInterface $member, ThreadRepositoryInterface $thread): PodiumResponse;

    public function unsubscribe(MemberRepositoryInterface $member, ThreadRepositoryInterface $thread): PodiumResponse;

    public function mark(MemberRepositoryInterface $member, PostRepositoryInterface $post): PodiumResponse;
}
