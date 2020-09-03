<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\interfaces\AccountInterface;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\GroupRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\MessageRepositoryInterface;
use bizley\podium\api\interfaces\PollRepositoryInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\Podium;
use bizley\podium\api\repositories\MemberRepository;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\web\User;

final class Account extends Component implements AccountInterface
{
    /**
     * @var string|array|MemberRepositoryInterface
     */
    public $repositoryConfig = MemberRepository::class;

    /**
     * @var string|array|User
     */
    public $userConfig = 'user';

    private ?Podium $podium = null;

    public function setPodium(Podium $podium): void
    {
        $this->podium = $podium;
    }

    /**
     * @throws InvalidConfigException
     */
    public function getPodium(): Podium
    {
        if (null === $this->podium) {
            throw new InvalidConfigException('Podium module is not set!');
        }

        return $this->podium;
    }

    /**
     * TODO: add private vars to store repos for multiple time usage.
     *
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function getMembership(): MemberRepositoryInterface
    {
        /** @var User $user */
        $user = Instance::ensure($this->userConfig, User::class);
        /** @var MemberRepository $member */
        $member = Instance::ensure($this->repositoryConfig, MemberRepositoryInterface::class);
        if (!$member->fetchOne($user->getId())) {
            throw new NoMembershipException('No Podium Membership found related to given identity!');
        }

        return $member;
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function joinGroup(GroupRepositoryInterface $group): PodiumResponse
    {
        return $this->getPodium()->group->join($group, $this->getMembership());
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function leaveGroup(GroupRepositoryInterface $group): PodiumResponse
    {
        return $this->getPodium()->group->leave($group, $this->getMembership());
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function createCategory(array $data = []): PodiumResponse
    {
        return $this->getPodium()->category->create($this->getMembership(), $data);
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function createForum(CategoryRepositoryInterface $category, array $data = []): PodiumResponse
    {
        return $this->getPodium()->forum->create($this->getMembership(), $category, $data);
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function createThread(ForumRepositoryInterface $forum, array $data = []): PodiumResponse
    {
        return $this->getPodium()->thread->create($this->getMembership(), $forum, $data);
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function createPost(ThreadRepositoryInterface $thread, array $data = []): PodiumResponse
    {
        return $this->getPodium()->post->create($this->getMembership(), $thread, $data);
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function createPoll(ThreadRepositoryInterface $thread, array $data = []): PodiumResponse
    {
        return $this->getPodium()->poll->create($this->getMembership(), $thread, $data);
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function markPost(PostRepositoryInterface $post): PodiumResponse
    {
        return $this->getPodium()->thread->mark($post, $this->getMembership());
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function subscribeThread(ThreadRepositoryInterface $thread): PodiumResponse
    {
        return $this->getPodium()->thread->subscribe($thread, $this->getMembership());
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function unsubscribeThread(ThreadRepositoryInterface $thread): PodiumResponse
    {
        return $this->getPodium()->thread->unsubscribe($thread, $this->getMembership());
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function thumbUpPost(PostRepositoryInterface $post): PodiumResponse
    {
        return $this->getPodium()->post->thumbUp($post, $this->getMembership());
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function thumbDownPost(PostRepositoryInterface $post): PodiumResponse
    {
        return $this->getPodium()->post->thumbDown($post, $this->getMembership());
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function thumbResetPost(PostRepositoryInterface $post): PodiumResponse
    {
        return $this->getPodium()->post->thumbReset($post, $this->getMembership());
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function votePoll(PollRepositoryInterface $poll, array $answer): PodiumResponse
    {
        return $this->getPodium()->poll->vote($poll, $this->getMembership(), $answer);
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function edit(array $data = []): PodiumResponse
    {
        return $this->getPodium()->member->edit($this->getMembership(), $data);
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function befriendMember(MemberRepositoryInterface $target): PodiumResponse
    {
        return $this->getPodium()->member->befriend($this->getMembership(), $target);
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function unfriendMember(MemberRepositoryInterface $target): PodiumResponse
    {
        return $this->getPodium()->member->unfriend($this->getMembership(), $target);
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function ignoreMember(MemberRepositoryInterface $target): PodiumResponse
    {
        return $this->getPodium()->member->ignore($this->getMembership(), $target);
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function sendMessage(
        MemberRepositoryInterface $receiver,
        MessageRepositoryInterface $replyTo = null,
        array $data = []
    ): PodiumResponse {
        return $this->getPodium()->message->send($this->getMembership(), $receiver, $replyTo, $data);
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function removeMessage(MessageRepositoryInterface $message): PodiumResponse
    {
        return $this->getPodium()->message->remove($message, $this->getMembership());
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function archiveMessage(MessageRepositoryInterface $message): PodiumResponse
    {
        return $this->getPodium()->message->archive($message, $this->getMembership());
    }

    /**
     * @throws InvalidConfigException
     * @throws NoMembershipException
     */
    public function reviveMessage(MessageRepositoryInterface $message): PodiumResponse
    {
        return $this->getPodium()->message->revive($message, $this->getMembership());
    }
}
