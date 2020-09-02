<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\interfaces\AccountInterface;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\GroupRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
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
    public function markPost(PostRepositoryInterface $post): PodiumResponse
    {
        return $this->getPodium()->thread->mark($post, $this->getMembership());
    }
}
