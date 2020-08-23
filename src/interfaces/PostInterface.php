<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

/**
 * Interface PostInterface.
 */
interface PostInterface
{
    /**
     * Creates post.
     */
    public function create(
        MemberRepositoryInterface $author,
        ThreadRepositoryInterface $thread,
        array $data = []
    ): PodiumResponse;

    /**
     * Updates post.
     */
    public function edit(PostRepositoryInterface $post, array $data = []): PodiumResponse;

    public function remove(PostRepositoryInterface $post): PodiumResponse;

    /**
     * Moves post to different thread.
     */
    public function move(PostRepositoryInterface $post, ThreadRepositoryInterface $thread): PodiumResponse;

    public function archive(PostRepositoryInterface $post): PodiumResponse;

    public function revive(PostRepositoryInterface $post): PodiumResponse;

    public function thumbUp(PostRepositoryInterface $post, MemberRepositoryInterface $member): PodiumResponse;

    public function thumbDown(PostRepositoryInterface $post, MemberRepositoryInterface $member): PodiumResponse;

    public function thumbReset(PostRepositoryInterface $post, MemberRepositoryInterface $member): PodiumResponse;
}
