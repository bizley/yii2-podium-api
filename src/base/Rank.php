<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RankInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

/**
 * Class Rank
 * @package bizley\podium\api\base
 */
class Rank extends Component implements RankInterface
{
    /**
     * @var string|array|ModelInterface rank handler
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $modelHandler = \bizley\podium\api\models\rank\Rank::class;

    /**
     * @var string|array|ModelFormInterface rank form handler
     * Component ID, class, configuration array, or instance of ModelFormInterface.
     */
    public $formHandler = \bizley\podium\api\models\rank\RankForm::class;

    /**
     * @var string|array|RemoverInterface rank remover handler
     * Component ID, class, configuration array, or instance of RemoverInterface.
     */
    public $removerHandler = \bizley\podium\api\models\rank\RankRemover::class;

    /**
     * @throws InvalidConfigException
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
        $rankClass = $this->modelHandler;

        return $rankClass::findById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getAll(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $rankClass = $this->modelHandler;

        return $rankClass::findByFilter($filter, $sort, $pagination);
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
     * Creates rank.
     * @param array $data
     * @return PodiumResponse
     */
    public function create(array $data): PodiumResponse
    {
        /* @var $rankForm ModelFormInterface */
        $rankForm = $this->getForm();

        if (!$rankForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $rankForm->create();
    }

    /**
     * Updates rank.
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

        $rankForm = $this->getForm((int)$id);

        if ($rankForm === null) {
            throw new ModelNotFoundException('Rank of given ID can not be found.');
        }

        if (!$rankForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $rankForm->edit();
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
     * Deletes rank.
     * @param int $id
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function remove(int $id): PodiumResponse
    {
        $rankRemover = $this->getRemover($id);

        if ($rankRemover === null) {
            throw new ModelNotFoundException('Rank of given ID can not be found.');
        }

        return $rankRemover->remove();
    }
}
