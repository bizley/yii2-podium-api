<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;

interface MemberInterface
{
    /**
     * Registers account.
     */
    public function register(array $data): PodiumResponse;

    public function remove($id): PodiumResponse;

    public function edit($id, array $data): PodiumResponse;

    /**
     * Befriends the member.
     */
    public function befriend($id, MemberRepositoryInterface $member): PodiumResponse;

    /**
     * Unfriends the member.
     */
    public function unfriend($id, MemberRepositoryInterface $member): PodiumResponse;

    /**
     * Ignores the member.
     */
    public function ignore($id, MemberRepositoryInterface $member): PodiumResponse;

    /**
     * Unignores the member.
     */
    public function unignore($id, MemberRepositoryInterface $member): PodiumResponse;

    public function ban($id): PodiumResponse;

    public function unban($id): PodiumResponse;

    public function join($id, GroupRepositoryInterface $group): PodiumResponse;

    public function leave($id, GroupRepositoryInterface $group): PodiumResponse;
}
