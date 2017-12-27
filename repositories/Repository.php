<?php

namespace bizley\podium\api\repositories;

use yii\db\ActiveRecord;

/**
 * General Podium Repository
 */
abstract class Repository extends ActiveRecord implements RepositoryInterface
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
        /* @var $repository Repository */
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
     * @inheritdoc
     */
    public function revise($data, $runValidation = true, $attributes = null)
    {
        if (!$this->loadData($data)) {
            return false;
        }
        return $this->update($runValidation, $attributes);
    }
}
