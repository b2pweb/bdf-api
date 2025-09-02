<?php

namespace Bdf\Api\Definition;

use PHPUnit\Framework\TestCase;

/**
 * Class ComplexTypeDefinitionTest
 *
 * @package Bdf\Api\Definition
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Definition
 * @group Bdf_Api_Definition_ComplexTypeDefinition
 *
 * @coversDefaultClass Bdf\Api\Definition\ComplexTypeDefinition
 */
class ComplexTypeDefinitionTest extends TestCase
{
    /**
     *
     */
    public function test_default()
    {
        $definition = new ComplexTypeDefinition('type1');

        $this->assertSame('type1', $definition->getName());
        $this->assertSame([], $definition->getProperties());
        $this->assertSame(null, $definition->getRealName());
    }

    /**
     * @dataProvider valuesProvider
     *
     * @param string $name
     * @param array  $properties
     */
    public function test_setters_getters($name, array $properties)
    {
        $definition = new ComplexTypeDefinition($name);

        $this->assertEquals($name, $definition->getName());

        if (array_key_exists('properties', $properties)) {
            $definition->setProperties($properties['properties']);

            $this->assertEquals($properties['properties'], $definition->getProperties());
        }

        if (array_key_exists('realName', $properties)) {
            $definition->setRealName($properties['realName']);

            $this->assertEquals($properties['realName'], $definition->getRealName());
        }
    }

    /**
     * @return array
     */
    public function valuesProvider()
    {
        return [
            [
                'Type1',
                [
                    'properties' => [
                        'name' => new PropertyDefinition('name', new SimpleTypeDefinition('string')),
                        'fromCity' => new PropertyDefinition('fromCity', new SimpleTypeDefinition('string')),
                        'toCity' => new PropertyDefinition('toCity', new SimpleTypeDefinition('string')),
                    ],
                    'realName'   => '\Api\Test\Type1',
                ]
            ],
            [
                'Type2',
                [
                    'properties' => [],
                    'realName'   => '\Api\Test\Type2222',
                ]
            ]
        ];
    }

    /**
     *
     */
    public function test_add_property()
    {
        $properties = [
            'first' => new PropertyDefinition('first', new SimpleTypeDefinition('string')),
            'second' => new PropertyDefinition('second', new SimpleTypeDefinition('string')),
        ];

        $propertiesToAdd = [
            'third' => new PropertyDefinition('third', new SimpleTypeDefinition('string')),
            'fourth' => new PropertyDefinition('fourth', new SimpleTypeDefinition('string')),
            'fifth' => new PropertyDefinition('fifth', new SimpleTypeDefinition('string')),
        ];

        $definition = new ComplexTypeDefinition('MyType', $properties);

        $this->assertEquals($properties, $definition->getProperties());

        foreach ($propertiesToAdd as $propertyToAdd) {
            $definition->addProperty($propertyToAdd);
        }

        $this->assertEquals(array_merge($properties, $propertiesToAdd), $definition->getProperties());
    }

    /**
     *
     */
    public function test_has_property()
    {
        $definition = new ComplexTypeDefinition('MyType', [
            'first' => new PropertyDefinition('first', new SimpleTypeDefinition('string')),
            'second' => new PropertyDefinition('second', new SimpleTypeDefinition('string')),
        ]);

        $this->assertFalse($definition->hasProperty('zero'));
        $this->assertTrue($definition->hasProperty('first'));
    }
}
