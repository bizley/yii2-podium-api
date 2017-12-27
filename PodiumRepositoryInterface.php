<?php

namespace bizley\podium\api;

use yii\db\ActiveRecordInterface;

/**
 * Podium Repository Interface
 */
interface PodiumRepositoryInterface extends ActiveRecordInterface
{
    /**
     * Loads data into the repository.
     * @param array $data array of pairs attribute's name => attribute's value
     * @return bool whether data has been loaded
     */
    public function loadData($data);

    /**
     * Fetches single repository data based on its primary key value.
     * @param mixed $primaryKey
     * @return bool whether fetch has been successful
     */
    public function fetch($primaryKey);

    /**
     * Adds new repository data.
     * @param array $data array of pairs attribute's name => attribute's value
     * @param bool $runValidation whether to perform repository validation before saving the record. Defaults to `true`.
     * If the validation fails, the record will not be saved to the database and this method will return `false`.
     * @param null|array $attributes list of attributes that need to be saved. Defaults to `null`, meaning all
     * attributes that are loaded will be saved.
     * @return bool whether the attributes are valid and the data is inserted successfully
     */
    public function add($data, $runValidation = true, $attributes = null);

    /**
     * Revises repository data.
     * @param array $data array of pairs attribute's name => attribute's value
     * @param bool $runValidation whether to perform repository validation before saving the record. Defaults to `true`.
     * If the validation fails, the record will not be saved to the database and this method will return `false`.
     * @param null|array $attributes list of attributes that need to be saved. Defaults to `null`, meaning all
     * attributes that are loaded will be saved.
     * @return bool|int
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function revise($data, $runValidation = true, $attributes = null);
}
