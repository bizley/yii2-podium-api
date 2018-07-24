<?php

declare(strict_types=1);

namespace bizley\podium\api\enums;

use Yii;

/**
 * Class Permission
 * @package bizley\podium\api\enums
 *
 * TODO: move to Podium client
 */
final class Permission extends BaseEnum
{
    public const CATEGORY_VIEW = 'category.view';
    public const CATEGORY_CREATE = 'category.create';
    public const CATEGORY_UPDATE = 'category.update';
    public const CATEGORY_DELETE = 'category.delete';

    public const FORUM_VIEW = 'forum.view';
    public const FORUM_CREATE = 'forum.create';
    public const FORUM_UPDATE = 'forum.update';
    public const FORUM_DELETE = 'forum.delete';
    public const FORUM_MOVE = 'forum.move';

    public const THREAD_VIEW = 'thread.view';
    public const THREAD_CREATE = 'thread.create';
    public const THREAD_UPDATE = 'thread.update';
    public const THREAD_DELETE = 'thread.delete';
    public const THREAD_PIN = 'thread.pin';
    public const THREAD_LOCK = 'thread.lock';
    public const THREAD_MOVE = 'thread.move';
    public const THREAD_SUBSCRIBE = 'thread.subscribe';

    public const POST_VIEW = 'post.view';
    public const POST_CREATE = 'post.create';
    public const POST_UPDATE = 'post.update';
    public const POST_DELETE = 'post.delete';
    public const POST_MOVE = 'post.move';

    public const POLL_VOTE = 'poll.vote';
    public const POLL_CREATE = 'poll.create';
    public const POLL_UPDATE = 'poll.update';
    public const POLL_DELETE = 'poll.delete';

    public const GROUP_VIEW = 'group.view';
    public const GROUP_CREATE = 'group.create';
    public const GROUP_UPDATE = 'group.update';
    public const GROUP_DELETE = 'group.delete';

    public const MEMBER_VIEW = 'member.view';
    public const MEMBER_BEFRIEND = 'member.befriend';
    public const MEMBER_BAN = 'member.ban';
    public const MEMBER_DELETE = 'member.delete';
    public const MEMBER_PROMOTE = 'member.promote';

    public const SETTINGS_CHANGE = 'settings.change';

    /**
     * {@inheritdoc}
     */
    public static function data(): array
    {
        return [
            self::CATEGORY_VIEW => Yii::t('podium.enum', 'permission.category.view'),
            self::CATEGORY_CREATE => Yii::t('podium.enum', 'permission.category.create'),
            self::CATEGORY_UPDATE => Yii::t('podium.enum', 'permission.category.update'),
            self::CATEGORY_DELETE => Yii::t('podium.enum', 'permission.category.delete'),
            self::FORUM_VIEW => Yii::t('podium.enum', 'permission.forum.view'),
            self::FORUM_CREATE => Yii::t('podium.enum', 'permission.forum.create'),
            self::FORUM_UPDATE => Yii::t('podium.enum', 'permission.forum.update'),
            self::FORUM_DELETE => Yii::t('podium.enum', 'permission.forum.delete'),
            self::FORUM_MOVE => Yii::t('podium.enum', 'permission.forum.move'),
            self::THREAD_VIEW => Yii::t('podium.enum', 'permission.thread.view'),
            self::THREAD_CREATE => Yii::t('podium.enum', 'permission.thread.create'),
            self::THREAD_UPDATE => Yii::t('podium.enum', 'permission.thread.update'),
            self::THREAD_DELETE => Yii::t('podium.enum', 'permission.thread.delete'),
            self::THREAD_PIN => Yii::t('podium.enum', 'permission.thread.pin'),
            self::THREAD_LOCK => Yii::t('podium.enum', 'permission.thread.lock'),
            self::THREAD_MOVE => Yii::t('podium.enum', 'permission.thread.move'),
            self::THREAD_SUBSCRIBE => Yii::t('podium.enum', 'permission.thread.subscribe'),
            self::POST_VIEW => Yii::t('podium.enum', 'permission.post.view'),
            self::POST_CREATE => Yii::t('podium.enum', 'permission.post.create'),
            self::POST_UPDATE => Yii::t('podium.enum', 'permission.post.update'),
            self::POST_DELETE => Yii::t('podium.enum', 'permission.post.delete'),
            self::POST_MOVE => Yii::t('podium.enum', 'permission.post.move'),
            self::POLL_VOTE => Yii::t('podium.enum', 'permission.poll.vote'),
            self::POLL_CREATE => Yii::t('podium.enum', 'permission.poll.create'),
            self::POLL_UPDATE => Yii::t('podium.enum', 'permission.poll.update'),
            self::POLL_DELETE => Yii::t('podium.enum', 'permission.poll.delete'),
            self::GROUP_VIEW => Yii::t('podium.enum', 'permission.group.view'),
            self::GROUP_CREATE => Yii::t('podium.enum', 'permission.group.create'),
            self::GROUP_UPDATE => Yii::t('podium.enum', 'permission.group.update'),
            self::GROUP_DELETE => Yii::t('podium.enum', 'permission.group.delete'),
            self::MEMBER_VIEW => Yii::t('podium.enum', 'permission.member.view'),
            self::MEMBER_BEFRIEND => Yii::t('podium.enum', 'permission.member.befriend'),
            self::MEMBER_BAN => Yii::t('podium.enum', 'permission.member.ban'),
            self::MEMBER_DELETE => Yii::t('podium.enum', 'permission.member.delete'),
            self::MEMBER_PROMOTE => Yii::t('podium.enum', 'permission.member.promote'),
            self::SETTINGS_CHANGE => Yii::t('podium.enum', 'permission.settings.change'),
        ];
    }
}
