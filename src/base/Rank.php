<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RankInterface;
use bizley\podium\api\interfaces\RemoverInterface;
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
class Rank extends PodiumComponent implements RankInterface
{
    /**
     * @var string|array|ModelInterface rank handler
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $rankHandler = \bizley\podium\api\models\rank\Rank::class;

    /**
     * @var string|array|ModelFormInterface rank form handler
     * Component ID, class, configuration array, or instance of ModelFormInterface.
     */
    public $rankFormHandler = \bizley\podium\api\models\rank\RankForm::class;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->rankHandler = Instance::ensure($this->rankHandler, ModelInterface::class);
        $this->rankFormHandler = Instance::ensure($this->rankFormHandler, ModelFormInterface::class);
    }

    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getRankById(int $id): ?ModelInterface
    {
        $rankClass = $this->rankHandler;

        return $rankClass::findById($id);
    }

    /**
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getRanks(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface
    {
        $rankClass = $this->rankHandler;

        return $rankClass::findByFilter($filter, $sort, $pagination);
    }

    /**
     * @param int|null $id
     * @return ModelFormInterface|null
     */
    public function getRankForm(?int $id = null): ?ModelFormInterface
    {
        $handler = $this->rankFormHandler;

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
        $rankForm = $this->getRankForm();

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

        $rankForm = $this->getRankForm((int)$id);

        if ($rankForm === null) {
            throw new ModelNotFoundException('Rank of given ID can not be found.');
        }

        if (!$rankForm->loadData($data)) {
            return PodiumResponse::error();
        }

        return $rankForm->edit();
    }

    /**
     * Deletes rank.
     * @param RemoverInterface $rankRemover
     * @return PodiumResponse
     */
    public function remove(RemoverInterface $rankRemover): PodiumResponse
    {
        return $rankRemover->remove();
    }
}
