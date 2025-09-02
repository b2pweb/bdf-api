<?php

namespace Bdf\Api\Definition;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

/**
 * Class PropertyDefinitionTest
 *
 * @package Bdf\Api\Definition
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Definition
 * @group Bdf_Api_Definition_PropertyDefinition
 *
 * @coversDefaultClass Bdf\Api\Definition\PropertyDefinition
 */
class PropertyDefinitionTest extends TestCase
{
    /**
     *
     */
    public function test_default()
    {
        $definition = new PropertyDefinition('property1', new SimpleTypeDefinition('string'));

        $this->assertSame('property1', $definition->getName());
        $this->assertSame(false, $definition->isNillable());
        $this->assertSame(false, $definition->isOptional());
        $this->assertEquals(new SimpleTypeDefinition('string'), $definition->getType());
    }

    /**
     * @dataProvider valuesProvider
     *
     * @param string $name
     * @param array  $properties
     */
    public function test_setters_getters($name, array $properties)
    {
        $definition = new PropertyDefinition(
            $name, array_key_exists('type', $properties) ? $properties['type'] : new SimpleTypeDefinition('string')
        );

        $this->assertEquals($name, $definition->getName());
        $this->assertEquals(array_key_exists('type', $properties) ? $properties['type'] : new SimpleTypeDefinition('string'), $definition->getType());

        if (array_key_exists('nillable', $properties)) {
            $definition->setNillable($properties['nillable']);

            $this->assertEquals((boolean) $properties['nillable'], $definition->isNillable());
        }

        if (array_key_exists('optional', $properties)) {
            $definition->setOptional($properties['optional']);

            $this->assertEquals((boolean) $properties['optional'], $definition->isOptional());
        }
    }

    /**
     * @return array
     */
    public function valuesProvider()
    {
        return [
            [
                'my_property1',
                [
                    'type'     => new SimpleTypeDefinition('string'),
                    'nillable' => null,
                    'optional' => null,
                ]
            ],
            [
                'my_property2',
                [
                    'type'     => new SimpleTypeDefinition('int'),
                    'nillable' => true,
                    'optional' => false,
                ]
            ],
            [
                'my_property3',
                [
                    'type'     => new SimpleTypeDefinition('string'),
                    'nillable' => false,
                    'optional' => true,
                ]
            ],
            [
                'my_property4',
                [
                    'type'     => new SimpleTypeDefinition('boolean'),
                    'nillable' => false,
                    'optional' => false,
                ]
            ]
        ];
    }

    /**
     *
     */
    public function test_change_name()
    {
        $definition = new PropertyDefinition('my_wrong_property_name', new SimpleTypeDefinition('string'));

        $this->assertEquals('my_wrong_property_name', $definition->getName());

        $definition->setName('my_right_property_name');

        $this->assertEquals('my_right_property_name', $definition->getName());
    }
}
