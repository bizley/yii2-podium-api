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
        array $data,
        MemberRepositoryInterface $author,
        ThreadRepositoryInterface $thread
    ): PodiumResponse;

    /**
     * Updates post.
     */
    public function edit($id, array $data): PodiumResponse;

    public function remove($id): PodiumResponse;

    /**
     * Moves post to different thread.
     */
    public function move($id, ThreadRepositoryInterface $thread): PodiumResponse;

    public function archive($id): PodiumResponse;

    public function revive($id): PodiumResponse;

    public function thumbUp(MemberRepositoryInterface $member, PostRepositoryInterface $post): PodiumResponse;

    public function thumbDown(MemberRepositoryInterface $member, PostRepositoryInterface $post): PodiumResponse;

    public function thumbReset(MemberRepositoryInterface $member, PostRepositoryInterface $post): PodiumResponse;
}
