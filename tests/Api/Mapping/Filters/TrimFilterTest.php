<?php

namespace Bdf\Api\Mapping\Filters;

use PHPUnit\Framework\TestCase;

/**
 * Class TrimFilterTest
 *
 * @package Bdf\Api\Mapping\Filters
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Filters
 * @group Bdf_Api_Mapping_Filters_TrimFilter
 *
 * @coversDefaultClass Bdf\Api\Mapping\Filters\TrimFilter
 */
class TrimFilterTest extends TestCase
{
    /**
     * @dataProvider provideTestFilter
     *
     * @param mixed $mask
     * @param mixed $value
     * @param mixed $expected
     */
    public function test_filter($mask, $value, $expected)
    {
        $this->assertEquals(
            $expected,
            (new TrimFilter($mask))->filter($value, $this->createMock('Bdf\Api\Mapping\ContextWrapperInterface'))
        );
    }

    /**
     * @return array
     */
    public function provideTestFilter()
    {
        return [
            [null, '  bonjour   ', 'bonjour'],
            ['5', '5ok5', 'ok']
        ];
    }
}
