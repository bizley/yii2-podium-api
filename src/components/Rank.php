<?php

declare(strict_types=1);

namespace bizley\podium\api\components;

use bizley\podium\api\InsufficientDataException;
use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RankInterface;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\models\rank\RankForm;
use bizley\podium\api\services\rank\RankRemover;
use yii\base\Component;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

final class Rank extends Component implements RankInterface
{
    /**
     * @var string|array|ModelInterface rank handler
     */
    public $modelHandler = \bizley\podium\api\models\rank\Rank::class;

    /**
     * @var string|array|ModelFormInterface rank form handler
     */
    public $formHandler = RankForm::class;

    /**
     * @var string|array|RemoverInterface rank remover handler
     */
    public $removerHandler = RankRemover::class;

    public function getById(int $id): ?ModelInterface
    {
        /** @var ModelInterface $rankClass */
        $rankClass = Instance::ensure($this->modelHandler, ModelInterface::class);

        return $rankClass::findById($id);
    }

    /**
     * @param bool|array|Sort|null       $sort
     * @param bool|array|Pagination|null $pagination
     */
    public function getAll(DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        /** @var ModelInterface $rankClass */
        $rankClass = Instance::ensure($this->modelHandler, ModelInterface::class);

        return $rankClass::findByFilter($filter, $sort, $pagination);
    }

    public function getForm(int $id = null): ?ModelFormInterface
    {
        /** @var ModelFormInterface $handler */
        $handler = Instance::ensure($this->formHandler, ModelFormInterface::class);
        if (null === $id) {
            return $handler;
        }
        /** @var ModelFormInterface|null $form */
        $form = $handler::findById($id);

        return $form;
    }

    /**
     * Creates rank.
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
     *
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function edit(array $data): PodiumResponse
    {
        $id = ArrayHelper::remove($data, 'id');
        if (null === $id) {
            throw new InsufficientDataException('ID key is missing.');
        }

        $rankForm = $this->getForm((int) $id);
        if (null === $rankForm) {
            throw new ModelNotFoundException('Rank of given ID can not be found.');
        }
        if (!$rankForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $rankForm->edit();
    }

    public function getRemover(): RemoverInterface
    {
        /** @var RemoverInterface $remover */
        $remover = Instance::ensure($this->removerHandler, RemoverInterface::class);

        return $remover;
    }

    /**
     * Deletes rank.
     */
    public function remove(int $id): PodiumResponse
    {
        return $this->getRemover()->remove($id);
    }
}
