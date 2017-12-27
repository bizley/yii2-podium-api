<?php

namespace bizley\podium\api\components;

use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\db\BaseActiveRecord;
use yii\di\Instance;

/**
 * General Podium Component
 *
 * @property string|array|null $id
 * @property array $errors
 */
abstract class Component extends \yii\base\Component
{
    /**
     * @var Podium
     */
    public $podium;

    /**
     * @var array|string|object Repository configuration
     */
    public $repositoryConfig;

    /**
     * @var BaseActiveRecord
     */
    public $repository;


    /**
     * Returns single repository object based on its primary key.
     * @param mixed $id
     * @return BaseActiveRecord|null
     * @throws InvalidConfigException
     */
    public function get($id)
    {
        $repository = $this->prepare();
        $this->repository = $repository::findOne($id);
        return $this->repository;
    }

    /**
     * Prepares repository object.
     * @return BaseActiveRecord
     * @throws InvalidConfigException
     */
    public function prepare()
    {
        $this->repository = Instance::ensure($this->repositoryConfig, BaseActiveRecord::class);
        return $this->repository;
    }

    /**
     * Loads data to the repository.
     * @param array $data array of pairs attribute's name => attribute's value
     * @return bool
     * @throws InvalidParamException
     */
    protected function load($data)
    {
        if ($this->repository === null) {
            return false;
        }
        return $this->repository->load($data, '');
    }

    /**
     * Adds new repository data.
     * @param array $data array of pairs attribute's name => attribute's value
     * @param bool $runValidation whether to perform repository validation before saving the record. Defaults to `true`.
     * If the validation fails, the record will not be saved to the database and this method will return `false`.
     * @param null|array $attributes list of attributes that need to be saved. Defaults to `null`, meaning all
     * attributes that are loaded will be saved.
     * @return bool
     * @throws InvalidConfigException
     * @throws InvalidParamException
     */
    public function add($data, $runValidation = true, $attributes = null)
    {
        $this->prepare();
        if (!$this->load($data)) {
            return false;
        }
        return $this->repository->insert($runValidation, $attributes);
    }

    /**
     * Updates repository data of given primary key.
     * @param mixed $id
     * @param array $data array of pairs attribute's name => attribute's value
     * @param bool $runValidation whether to perform repository validation before saving the record. Defaults to `true`.
     * If the validation fails, the record will not be saved to the database and this method will return `false`.
     * @param null|array $attributes list of attributes that need to be saved. Defaults to `null`, meaning all
     * attributes that are loaded will be saved.
     * @return bool|int
     * @throws InvalidConfigException
     * @throws InvalidParamException
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function update($id, $data, $runValidation = true, $attributes = null)
    {
        $this->get($id);
        if (!$this->load($data)) {
            return false;
        }
        return $this->repository->update($runValidation, $attributes);
    }

    /**
     * Deletes repository data of given primary key.
     * @param mixed $id
     * @return int|bool
     * @throws InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function delete($id)
    {
        $this->get($id);
        if ($this->repository === null) {
            return false;
        }
        return $this->repository ? $this->repository->delete() : false;
    }

    /**
     * Returns repository primary key value.
     * @return mixed
     */
    public function getId()
    {
        return $this->repository ? $this->repository->getPrimaryKey() : null;
    }

    /**
     * Returns the errors for all repository attributes or a single attribute when getErrors method is available for
     * repository or empty array otherwise.
     * @param null|string $attribute attribute name. Use null to retrieve errors for all attributes.
     * @return array
     */
    public function getErrors($attribute = null)
    {
        return $this->repository ? $this->repository->getErrors($attribute) : [];
    }
}
