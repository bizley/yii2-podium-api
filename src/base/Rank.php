<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RankInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\models\rank\RankForm;
use bizley\podium\api\models\rank\RankRemover;
use yii\base\Component;
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
final class Rank extends Component implements RankInterface
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
    public $formHandler = RankForm::class;

    /**
     * @var string|array|RemoverInterface rank remover handler
     * Component ID, class, configuration array, or instance of RemoverInterface.
     */
    public $removerHandler = RankRemover::class;

    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getById(int $id): ?ModelInterface
    {
        /** @var ModelInterface $rankClass */
        $rankClass = Instance::ensure($this->modelHandler, ModelInterface::class);
        return $rankClass::findById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getAll(DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        /** @var ModelInterface $rankClass */
        $rankClass = Instance::ensure($this->modelHandler, ModelInterface::class);
        return $rankClass::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @param int|null $id
     * @return ModelFormInterface|null
     */
    public function getForm(int $id = null): ?ModelFormInterface
    {
        /** @var ModelFormInterface $handler */
        $handler = Instance::ensure($this->formHandler, ModelFormInterface::class);
        if ($id === null) {
            return $handler;
        }
        /** @var ModelFormInterface|null $form */
        $form = $handler::findById($id);
        return $form;
    }

    /**
     * Creates rank.
     * @param array $data
     * @return PodiumResponse
     */
    public function create(array $data): PodiumResponse
    {
        /** @var ModelFormInterface $rankForm */
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
        /** @var RemoverInterface $handler */
        $handler = Instance::ensure($this->removerHandler, RemoverInterface::class);
        /** @var RemoverInterface|null $remover */
        $remover = $handler::findById($id);
        return $remover;
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
