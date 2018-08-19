<?php

declare(strict_types=1);

namespace bizley\podium\api\models\group;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\repos\GroupRepo;
use yii\base\NotSupportedException;

/**
 * Class Group
 * @package bizley\podium\api\models\group
 */
class Group extends GroupRepo implements ModelInterface
{
    use ModelTrait;

    /**
     * @return ModelInterface
     * @throws NotSupportedException
     */
    public function getParent(): ModelInterface
    {
        throw new NotSupportedException('Group has got no parent.');
    }

    /**
     * @return int
     * @throws NotSupportedException
     */
    public function getPostsCount(): int
    {
        throw new NotSupportedException('Group has got no posts.');
    }

    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function isArchived(): bool
    {
        throw new NotSupportedException('Group can not be archived.');
    }
}
