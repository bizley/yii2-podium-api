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
use yii\helpers\ArrayHelper;

/**
 * Class Group
 * @package bizley\podium\api\base
 */
class Group extends PodiumComponent implements GroupInterface
{
    /**
     * @var string|array|ModelInterface group handler
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $groupHandler = \bizley\podium\api\models\group\Group::class;

    /**
     * @var string|array|ModelFormInterface group form handler
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
     * @param int|null $id
     * @return ModelFormInterface|null
     */
    public function getGroupForm(?int $id = null): ?ModelFormInterface
    {
        $handler = $this->groupFormHandler;

        if ($id === null) {
            return new $handler;
        }

        return $handler::findById($id);
    }

    /**
     * Creates group.
     * @param array $data
     * @return PodiumResponse
     */
    public function create(array $data): PodiumResponse
    {
        /* @var $groupForm ModelFormInterface */
        $groupForm = $this->getGroupForm();

        if (!$groupForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $groupForm->create();
    }

    /**
     * Updates group.
     * @param array $data
     * @return PodiumResponse
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function edit(array $data): PodiumResponse
    {
        $id = ArrayHelper::remove($data, 'id');

        if ($id === null) {
            throw new InsufficientDataException('ID key is missing.');
        }

        $groupForm = $this->getGroupForm((int)$id);

        if ($groupForm === null) {
            throw new ModelNotFoundException('Group of given ID can not be found.');
        }

        if (!$groupForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $groupForm->edit();
    }

    /**
     * Deletes group.
     * @param RemovableInterface $groupRemover
     * @return PodiumResponse
     */
    public function remove(RemovableInterface $groupRemover): PodiumResponse
    {
        return $groupRemover->remove();
    }
}
