<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\models\category\CategoryForm;
use yii\base\Event;

/**
 * Class CategoryEvent
 * @package bizley\podium\api\events
 */
class CategoryEvent extends Event
{
    /**
     * @var bool whether category can be created
     */
    public $canCreate = true;

    /**
     * @var bool whether category can be edited
     */
    public $canEdit = true;

    /**
     * @var CategoryForm
     */
    public $category;
}
