<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface MemberInterface
{
    /**
     * Registers account.
     *
     * @param int|string|array $id
     */
    public function register($id, array $data = []): PodiumResponse;

    public function remove(MemberRepositoryInterface $member): PodiumResponse;

    public function edit(MemberRepositoryInterface $member, array $data = []): PodiumResponse;

    /**
     * Befriends the member.
     */
    public function befriend(MemberRepositoryInterface $member, MemberRepositoryInterface $target): PodiumResponse;

    /**
     * Unfriends the member.
     */
    public function unfriend(MemberRepositoryInterface $member, MemberRepositoryInterface $target): PodiumResponse;

    /**
     * Ignores the member.
     */
    public function ignore(MemberRepositoryInterface $member, MemberRepositoryInterface $target): PodiumResponse;

    /**
     * Unignores the member.
     */
    public function unignore(MemberRepositoryInterface $member, MemberRepositoryInterface $target): PodiumResponse;

    public function ban(MemberRepositoryInterface $member): PodiumResponse;

    public function unban(MemberRepositoryInterface $member): PodiumResponse;
}
