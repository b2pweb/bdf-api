<?php

namespace Bdf\Api\Mapping;

use PHPUnit\Framework\TestCase;

/**
 * Class RegistryTest
 *
 * @package Bdf\Api\Mapping
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Registry
 */
class RegistryTest extends TestCase
{
    /**
     *
     */
    public function test_getFilter_with_array()
    {
        $registry = new Registry();

        $filter = $registry->getFilter(['trim', ' ']);

        $this->assertEquals(
            'Test',
            $filter->filter('     Test  ', $this->createMock('Bdf\Api\Mapping\ContextWrapperInterface'))
        );
    }

    /**
     *
     */
    public function test_getFilter_with_callable()
    {
        $registry = new Registry();

        $filter = $registry->getFilter(function($value) {
            return 'Bonjour ' . $value;
        });

        $this->assertEquals(
            'Bonjour Test',
            $filter->filter('Test', $this->createMock('Bdf\Api\Mapping\ContextWrapperInterface'))
        );
    }

    /**
     *
     */
    public function test_getFilter_with_string()
    {
        $registry = new Registry();

        $filter = $registry->getFilter('trim');

        $this->assertEquals(
            'Test',
            $filter->filter("  \n   Test  ", $this->createMock('Bdf\Api\Mapping\ContextWrapperInterface'))
        );
    }
}
