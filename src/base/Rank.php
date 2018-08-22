<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\interfaces\ModelFormInterface;
use bizley\podium\api\interfaces\ModelInterface;
use bizley\podium\api\interfaces\RankInterface;
use bizley\podium\api\interfaces\RemovableInterface;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;
use yii\di\Instance;

/**
 * Class Rank
 * @package bizley\podium\api\base
 */
class Rank extends PodiumComponent implements RankInterface
{
    /**
     * @var string|array|ModelInterface
     * Component ID, class, configuration array, or instance of ModelInterface.
     */
    public $rankHandler = \bizley\podium\api\models\rank\Rank::class;

    /**
     * @var string|array|ModelFormInterface
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
     * @return ModelFormInterface
     */
    public function getRankForm(): ModelFormInterface
    {
        return new $this->rankFormHandler;
    }

    /**
     * @param array $data
     * @return PodiumResponse
     */
    public function create(array $data): PodiumResponse
    {
        $rankForm = $this->getRankForm();

        if (!$rankForm->loadData($data)) {
            return PodiumResponse::error();
        }
        return $rankForm->create();
    }

    /**
     * @param ModelFormInterface $rankForm
     * @param array $data
     * @return PodiumResponse
     */
    public function edit(ModelFormInterface $rankForm, array $data): PodiumResponse
    {
        if (!$rankForm->loadData($data)) {
            return PodiumResponse::error();
        }
        return $rankForm->edit();
    }

    /**
     * @param RemovableInterface $rankRemover
     * @return PodiumResponse
     */
    public function remove(RemovableInterface $rankRemover): PodiumResponse
    {
        return $rankRemover->remove();
    }
}
