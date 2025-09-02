<?php

namespace Bdf\Api\Definition;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

/**
 * Class ParameterDefinitionTest
 *
 * @package Bdf\Api\Definition
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Definition
 * @group Bdf_Api_Definition_ParameterDefinition
 *
 * @coversDefaultClass Bdf\Api\Definition\ParameterDefinition
 */
class ParameterDefinitionTest extends TestCase
{
    /**
     *
     */
    public function test_default()
    {
        $definition = new ParameterDefinition('param1', new SimpleTypeDefinition('string'));

        $this->assertSame(null, $definition->getDefaultValue());
        $this->assertSame(null, $definition->getDescription());
        $this->assertSame('param1', $definition->getName());
        $this->assertSame(false, $definition->isOptional());
        $this->assertSame(null, $definition->getPosition());
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
        $definition = new ParameterDefinition(
            $name, array_key_exists('type', $properties) ? $properties['type'] : new SimpleTypeDefinition('string')
        );

        $this->assertEquals($name, $definition->getName());
        $this->assertEquals(array_key_exists('type', $properties) ? $properties['type'] : new SimpleTypeDefinition('string'), $definition->getType());

        if (array_key_exists('position', $properties)) {
            $definition->setPosition($properties['position']);

            $this->assertEquals($properties['position'], $definition->getPosition());
        }

        if (array_key_exists('defaultValue', $properties)) {
            $definition->setDefaultValue($properties['defaultValue']);

            $this->assertEquals($properties['defaultValue'], $definition->getDefaultValue());
        }

        if (array_key_exists('optional', $properties)) {
            $definition->setOptional($properties['optional']);

            $this->assertEquals((boolean) $properties['optional'], $definition->isOptional());
        }

        if (array_key_exists('description', $properties)) {
            $definition->setDescription($properties['description']);

            $this->assertEquals($properties['description'], $definition->getDescription());
        }
    }

    /**
     * @return array
     */
    public function valuesProvider()
    {
        return [
            [
                'my_parameter1',
                [
                    'position'      => 1,
                    'type'          => new SimpleTypeDefinition('string'),
                    'defaultValue'  => null,
                    'optional'      => null,
                    'description'   => 'Test description'
                ]
            ],
            [
                'my_parameter2',
                [
                    'position'      => 2,
                    'type'          => new SimpleTypeDefinition('int'),
                    'defaultValue'  => 5,
                    'optional'      => false,
                ]
            ],
            [
                'my_parameter3',
                [
                    'position'      => 3,
                    'type'          => new SimpleTypeDefinition('string'),
                    'defaultValue'  => 'bonjour',
                    'optional'      => true,
                ]
            ],
            [
                'my_parameter4',
                [
                    'position'      => 1,
                    'type'          => new SimpleTypeDefinition('boolean'),
                    'optional'      => false,
                ]
            ]
        ];
    }

    /**
     *
     */
    public function test_change_name()
    {
        $definition = new ParameterDefinition('my_wrong_parameter_name', new SimpleTypeDefinition('string'));

        $this->assertEquals('my_wrong_parameter_name', $definition->getName());

        $definition->setName('my_right_parameter_name');

        $this->assertEquals('my_right_parameter_name', $definition->getName());
    }
}
