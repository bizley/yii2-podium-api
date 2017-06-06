<?php

namespace bizley\podium\api\tests;

use bizley\podium\api\tests\props\TestDictionary;

class DictionaryTest extends \PHPUnit\Framework\TestCase
{
    public function testGet()
    {
        $this->assertEquals('First', TestDictionary::get(TestDictionary::FIRST));
        $this->assertEquals('Second', TestDictionary::get(TestDictionary::SECOND));
        $this->assertEquals('Third', TestDictionary::get(TestDictionary::THIRD));
        $this->assertNull(TestDictionary::get('non-existing'));
    }

    public function testRange()
    {
        $this->assertEquals([TestDictionary::FIRST, TestDictionary::SECOND, TestDictionary::THIRD], TestDictionary::range());
    }
}