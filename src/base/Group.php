<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\GroupInterface;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RemoverInterface;
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
    public $modelHandler = \bizley\podium\api\models\group\Group::class;

    /**
     * @var string|array|ModelFormInterface group form handler
     * Component ID, class, configuration array, or instance of ModelFormInterface.
     */
    public $formHandler = \bizley\podium\api\models\group\GroupForm::class;

    /**
     * @var string|array|RemoverInterface group remover handler
     * Component ID, class, configuration array, or instance of RemovableInterface.
     */
    public $removerHandler = \bizley\podium\api\models\group\GroupRemover::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->modelHandler = Instance::ensure($this->modelHandler, ModelInterface::class);
        $this->formHandler = Instance::ensure($this->formHandler, ModelFormInterface::class);
        $this->removerHandler = Instance::ensure($this->removerHandler, RemoverInterface::class);
    }

    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getById(int $id): ?ModelInterface
    {
        $groupClass = $this->modelHandler;

        return $groupClass::findById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getAll(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $groupClass = $this->modelHandler;

        return $groupClass::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @param int|null $id
     * @return ModelFormInterface|null
     */
    public function getForm(?int $id = null): ?ModelFormInterface
    {
        $handler = $this->formHandler;

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
        $groupForm = $this->getForm();

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

        $groupForm = $this->getForm((int)$id);

        if ($groupForm === null) {
            throw new ModelNotFoundException('Group of given ID can not be found.');
        }

        if (!$groupForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $groupForm->edit();
    }

    /**
     * @param int $id
     * @return RemoverInterface|null
     */
    public function getRemover(int $id): ?RemoverInterface
    {
        $handler = $this->removerHandler;

        return $handler::findById($id);
    }

    /**
     * Deletes group.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function remove(int $id): PodiumResponse
    {
        $groupRemover = $this->getRemover($id);

        if ($groupRemover === null) {
            throw new ModelNotFoundException('Group of given ID can not be found.');
        }

        return $groupRemover->remove();
    }
}
