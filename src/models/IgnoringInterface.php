<?php

declare(strict_types=1);

namespace bizley\podium\api\models;

/**
 * Interface IgnoringInterface
 * @package bizley\podium\api\models
 */
interface IgnoringInterface
{
    public const EVENT_BEFORE_IGNORING = 'podium.acquaintance.ignoring.before';
    public const EVENT_AFTER_IGNORING = 'podium.acquaintance.ignoring.after';
    public const EVENT_BEFORE_UNIGNORING = 'podium.acquaintance.unignoring.before';
    public const EVENT_AFTER_UNIGNORING = 'podium.acquaintance.unignoring.after';

    /**
     * Initiator of ignoring.
     * @param int $memberId Podium account ID
     */
    public function setMember(int $memberId): void;

    /**
     * Target of ignoring.
     * @param int $targetId Podium account ID
     */
    public function setTarget(int $targetId): void;

    /**
     * This method should be called before ignoring and it should trigger EVENT_BEFORE_IGNORING event.
     * @return bool whether member can ignore target
     */
    public function beforeIgnore(): bool;

    /**
     * Handles ignoring process.
     * @return bool whether ignoring was successful
     */
    public function ignore(): bool;

    /**
     * This method should be called after ignoring and it should trigger EVENT_AFTER_IGNORING event.
     */
    public function afterIgnore(): void;

    /**
     * This method should be called before unignoring and it should trigger EVENT_BEFORE_UNIGNORING event.
     * @return bool whether member can unignore target
     */
    public function beforeUnignore(): bool;

    /**
     * Handles unignoring process.
     * @return bool whether unignoring was successful
     */
    public function unignore(): bool;

    /**
     * This method should be called after unignoring and it should trigger EVENT_AFTER_UNIGNORING event.
     */
    public function afterUnignore(): void;

    /**
     * @return bool whether member is ignoring target
     */
    public function isIgnoring(): bool;
}
