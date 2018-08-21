<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\GroupInterface;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RemovableInterface;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;
use yii\di\Instance;

/**
 * Class Group
 * @package bizley\podium\api\base
 */
class Group extends PodiumComponent implements GroupInterface
{
    /**
     * @var string|array|ModelInterface
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $groupHandler = \bizley\podium\api\models\group\Group::class;

    /**
     * @var string|array|ModelFormInterface
     * Component ID, class, configuration array, or instance of ModelFormInterface.
     */
    public $groupFormHandler = \bizley\podium\api\models\group\GroupForm::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->groupHandler = Instance::ensure($this->groupHandler, ModelInterface::class);
        $this->groupFormHandler = Instance::ensure($this->groupFormHandler, ModelFormInterface::class);
    }

    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getGroupById(int $id): ?ModelInterface
    {
        $groupClass = $this->groupHandler;
        return $groupClass::findById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getGroups(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $groupClass = $this->groupHandler;
        return $groupClass::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @return ModelFormInterface
     */
    public function getGroupForm(): ModelFormInterface
    {
        return new $this->groupFormHandler;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool
    {
        $groupForm = $this->getGroupForm();

        if (!$groupForm->loadData($data)) {
            return false;
        }
        return $groupForm->create();
    }

    /**
     * @param ModelFormInterface $groupForm
     * @param array $data
     * @return bool
     */
    public function edit(ModelFormInterface $groupForm, array $data): bool
    {
        if (!$groupForm->loadData($data)) {
            return false;
        }
        return $groupForm->edit();
    }

    /**
     * @param RemovableInterface $groupRemover
     * @return bool
     */
    public function remove(RemovableInterface $groupRemover): bool
    {
        return $groupRemover->remove();
    }
}
