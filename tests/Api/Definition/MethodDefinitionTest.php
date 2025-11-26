<?php

namespace Bdf\Api\Definition;

use PHPUnit\Framework\TestCase;

/**
 * Class MethodDefinitionTest
 *
 * @package Bdf\Api\Definition
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Definition
 * @group Bdf_Api_Definition_MethodDefinition
 *
 * @coversDefaultClass Bdf\Api\Definition\MethodDefinition
 */
class MethodDefinitionTest extends TestCase
{
    /**
     *
     */
    public function test_default()
    {
        $definition = new MethodDefinition('test');

        $this->assertSame('test', $definition->getName());
        $this->assertSame(null, $definition->getDescription());
        $this->assertSame([], $definition->getParameters());
        $this->assertSame(null, $definition->getReturn());
        $this->assertSame(false, $definition->isThrowingException());
    }

    /**
     * @dataProvider valuesProvider
     *
     * @param string $name
     * @param array  $properties
     */
    public function test_setters_getters($name, array $properties)
    {
        $definition = new MethodDefinition($name);

        $this->assertEquals($name, $definition->getName());

        if (array_key_exists('description', $properties)) {
            $definition->setDescription($properties['description']);

            $this->assertEquals($properties['description'], $definition->getDescription());
        }

        if (array_key_exists('parameters', $properties)) {
            $definition->setParameters($properties['parameters']);

            $this->assertEquals($properties['parameters'], $definition->getParameters());
        }

        if (array_key_exists('return', $properties)) {
            $definition->setReturn($properties['return']);

            $this->assertEquals($properties['return'], $definition->getReturn());
        }

        if (array_key_exists('throwingException', $properties)) {
            $definition->setThrowingException((bool) $properties['throwingException']);

            $this->assertEquals((bool) $properties['throwingException'], $definition->isThrowingException());
        }
    }

    /**
     * @return array
     */
    public function valuesProvider()
    {
        return [
            [
                'my_method1',
                [
                    'description'       => 'Description de ma méthode',
                    'parameters'        => [
                        new ParameterDefinition('name', new SimpleTypeDefinition('string')),
                        new ParameterDefinition('value', new SimpleTypeDefinition('string'))
                    ],
                    'return'            => new SimpleTypeDefinition('int'),
                    'throwingException' => false,
                ]
            ],
            [
                'my_method2',
                [
                    'parameters'        => [],
                    'return'            => null,
                    'throwingException' => true,
                ]
            ]
        ];
    }

    /**
     *
     */
    public function test_change_name()
    {
        $definition = new MethodDefinition('my_wrong_method_name');

        $this->assertEquals('my_wrong_method_name', $definition->getName());

        $definition->setName('my_right_method_name');

        $this->assertEquals('my_right_method_name', $definition->getName());
    }

    /**
     *
     */
    public function test_add_parameter()
    {
        $parameters = [
            new ParameterDefinition('firt_parameter', new SimpleTypeDefinition('string')),
            new ParameterDefinition('second_parameter', new SimpleTypeDefinition('string')),
            new ParameterDefinition('third_parameter', new SimpleTypeDefinition('string'))
        ];

        $definition = new MethodDefinition('my_method');

        foreach ($parameters as $parameter) {
            $definition->addParameter($parameter);
        }

        $this->assertEquals($parameters, $definition->getParameters());
    }
}
