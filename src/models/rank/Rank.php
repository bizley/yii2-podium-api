<?php

declare(strict_types=1);

namespace bizley\podium\api\models\rank;

use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\models\ModelTrait;
use bizley\podium\api\repos\RankRepo;
use yii\base\NotSupportedException;

/**
 * Class Category
 * @package bizley\podium\api\models\rank
 */
class Rank extends RankRepo implements ModelInterface
{
    use ModelTrait;

    /**
     * @return ModelInterface|null
     * @throws NotSupportedException
     */
    public function getParent(): ?ModelInterface
    {
        throw new NotSupportedException('Rank has got no parent.');
    }

    /**
     * @return int
     * @throws NotSupportedException
     */
    public function getPostsCount(): int
    {
        throw new NotSupportedException('Rank has got no posts.');
    }

    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function isArchived(): bool
    {
        throw new NotSupportedException('Rank can not be archived.');
    }
}
