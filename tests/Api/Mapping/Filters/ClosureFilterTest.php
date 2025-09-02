<?php

namespace Bdf\Api\Mapping\Filters;

use PHPUnit\Framework\TestCase;

/**
 * Class ClosureFilterTest
 *
 * @package Bdf\Api\Mapping\Filters
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Filters
 * @group Bdf_Api_Mapping_Filters_ClosureFilter
 *
 * @coversDefaultClass Bdf\Api\Mapping\Filters\ClosureFilter
 */
class ClosureFilterTest extends TestCase
{
    /**
     *
     */
    public function test_filter()
    {
        $filter = new ClosureFilter(function($value) {
            return $value * 10;
        });

        $this->assertEquals(20, $filter->filter(2, $this->createMock('Bdf\Api\Mapping\ContextWrapperInterface')));
    }
}
