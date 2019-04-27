<?php

declare(strict_types=1);

namespace bizley\podium\api\models\member;

use bizley\podium\api\interfaces\MembershipInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\repos\MemberRepo;
use bizley\podium\api\repos\PostRepo;
use yii\base\NotSupportedException;

/**
 * Class Member
 * @package bizley\podium\api\models\member
 *
 * @property ModelInterface $parent
 * @property int $postsCount
 */
class Member extends MemberRepo implements MembershipInterface
{
    use ModelTrait;

    /**
     * @param int|string $userId
     * @return MembershipInterface|null
     */
    public static function findByUserId($userId): ?MembershipInterface
    {
        return static::findOne(['user_id' => $userId]);
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return ModelInterface|null
     * @throws NotSupportedException
     */
    public function getParent(): ?ModelInterface
    {
        throw new NotSupportedException('Member has got no parent.');
    }

    /**
     * @return int
     * @throws NotSupportedException
     */
    public function getPostsCount(): int
    {
        $counter = PostRepo::find()->where(['author_id' => $this->getId()])->count();

        if ($counter > PHP_INT_MAX) {
            throw new NotSupportedException('Your system can not handle integer that big.');
        }

        return (int)$counter;
    }

    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function isArchived(): bool
    {
        throw new NotSupportedException('Member can not be archived.');
    }
}
