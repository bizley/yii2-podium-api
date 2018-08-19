<?php

declare(strict_types=1);

namespace bizley\podium\tests\enums;

use bizley\podium\api\enums\PostType;
use bizley\podium\tests\TestCase;

/**
 * Class PostTypeTest
 * @package bizley\podium\tests\enums
 */
class PostTypeTest extends TestCase
{
    public function testData(): void
    {
        $this->assertEquals([
            PostType::POST => 'post.type.post',
            PostType::POLL => 'post.type.poll',
        ], PostType::data());
    }
}
