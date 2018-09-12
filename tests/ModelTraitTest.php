<?php

declare(strict_types=1);

namespace bizley\podium\tests;

use bizley\podium\api\models\ModelTrait;
use yii\base\InvalidArgumentException;
use yii\data\Pagination;
use yii\data\Sort;
use yii\db\ActiveRecord;

/**
 * Class ModelTraitTest
 * @package bizley\podium\tests
 */
class ModelTraitTest extends DbTestCase
{
    public function testSort(): void
    {
        $provider = ExampleModel::findByFilter(null, new Sort());

        $this->assertNotEmpty($provider->getSort());
    }

    public function testPagination(): void
    {
        $provider = ExampleModel::findByFilter(null, null, new Pagination());

        $this->assertNotEmpty($provider->getPagination());
    }

    public function testConvertWrongClass(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ExampleModel())->convert(AnotherExampleModel::class);
    }
}

class ExampleModel extends ActiveRecord
{
    use ModelTrait;

    public static function tableName(): string
    {
        return '{{%podium_forum}}';
    }
}

class AnotherExampleModel
{
    public static function tableName(): string
    {
        return 'another_name';
    }
}
