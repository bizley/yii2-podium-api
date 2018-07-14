<?php

declare(strict_types=1);

namespace bizley\podium\api\models;

/**
 * Interface FriendshipInterface
 * @package bizley\podium\api\models
 */
interface FriendshipInterface
{
    public const EVENT_BEFORE_BEFRIENDING = 'podium.acquaintance.befriending.before';
    public const EVENT_AFTER_BEFRIENDING = 'podium.acquaintance.befriending.after';
    public const EVENT_BEFORE_UNFRIENDING = 'podium.acquaintance.unfriending.before';
    public const EVENT_AFTER_UNFRIENDING = 'podium.acquaintance.unfriending.after';

    /**
     * Initiator of friendship.
     * @param int $memberId Podium account ID
     */
    public function setMember(int $memberId): void;

    /**
     * Target of friendship.
     * @param int $targetId Podium account ID
     */
    public function setTarget(int $targetId): void;

    /**
     * This method should be called before befriending and it should trigger EVENT_BEFORE_BEFRIENDING event.
     * @return bool whether member and target can be friends
     */
    public function beforeBefriend(): bool;

    /**
     * Handles befriending process.
     * @return bool whether befriending was successful
     */
    public function befriend(): bool;

    /**
     * This method should be called after befriending and it should trigger EVENT_AFTER_BEFRIENDING event.
     */
    public function afterBefriend(): void;

    /**
     * This method should be called before unfriending and it should trigger EVENT_BEFORE_UNFRIENDING event.
     * @return bool whether member can unfriend target
     */
    public function beforeUnfriend(): bool;

    /**
     * Handles unfriending process.
     * @return bool whether unfriending was successful
     */
    public function unfriend(): bool;

    /**
     * This method should be called after unfriending and it should trigger EVENT_AFTER_UNFRIENDING event.
     */
    public function afterUnfriend(): void;

    /**
     * @return bool whether target is a friend of member
     */
    public function isFriend(): bool;
}
