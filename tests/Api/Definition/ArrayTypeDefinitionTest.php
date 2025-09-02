<?php

namespace Bdf\Api\Definition;

use PHPUnit\Framework\TestCase;

/**
 * Class ArrayTypeDefinitionTest
 *
 * @package Bdf\Api\Definition
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Definition
 * @group Bdf_Api_Definition_ArrayTypeDefinition
 *
 * @coversDefaultClass Bdf\Api\Definition\ArrayTypeDefinition
 */
class ArrayTypeDefinitionTest extends TestCase
{
    /**
     * @dataProvider valuesProvider
     *
     * @param string         $name
     * @param TypeDefinition $type
     */
    public function test_getters($name, TypeDefinition $type)
    {
        $definition = new ArrayTypeDefinition($type);

        $this->assertEquals($name, $definition->getName());
        $this->assertEquals($type, $definition->getInternalType());
    }

    /**
     * @return array
     */
    public function valuesProvider()
    {
        return [
            [
                'int[]',
                new SimpleTypeDefinition('int')
            ],
            [
                'Offer[]',
                new ComplexTypeDefinition('Offer')
            ]
        ];
    }
}
