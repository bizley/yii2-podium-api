<?php

namespace bizley\podium\api;

use yii\db\ActiveRecord;

/**
 * General Podium Repository
 */
abstract class PodiumRepository extends ActiveRecord implements PodiumRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function loadData($data)
    {
        return $this->load($data, '');
    }

    /**
     * @inheritdoc
     */
    public function fetch($primaryKey)
    {
        $repoClass = get_class($this);
        /* @var $repository PodiumRepository */
        $repository = $repoClass::findOne($primaryKey);
        if (null === $repository) {
            return false;
        }
        $this->setAttributes($repository->getAttributes(), false);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function add($data, $runValidation = true, $attributes = null)
    {
        if (!$this->loadData($data)) {
            return false;
        }
        return $this->insert($runValidation, $attributes);
    }

    /**
     * {@inheritdoc}
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function revise($data, $runValidation = true, $attributes = null)
    {
        if (!$this->loadData($data)) {
            return false;
        }
        return $this->update($runValidation, $attributes);
    }
}
