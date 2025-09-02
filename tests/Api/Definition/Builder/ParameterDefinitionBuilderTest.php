<?php

namespace Bdf\Api\Definition\Builder;

use Bdf\Api\Definition\ArrayTypeDefinition;
use Bdf\Api\Definition\ParameterDefinition;
use Bdf\Api\Definition\SimpleTypeDefinition;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

/**
 * Class ParameterDefinitionBuilderTest
 *
 * @package Bdf\Api\Definition\Builder
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Definition
 * @group Bdf_Api_Definition_Builder
 * @group Bdf_Api_Definition_Builder_ParameterDefinitionBuilder
 *
 * @coversDefaultClass Bdf\Api\Definition\Builder\ParameterDefinitionBuilder
 */
class ParameterDefinitionBuilderTest extends TestCase
{
    /**
     * @var ParameterDefinitionBuilder
     */
    protected $builder;


    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->builder = new ParameterDefinitionBuilder();
    }

    /**
     * @dataProvider settersProvider
     *
     * @param array $values
     */
    public function test_setters(array $values)
    {
        $definition = $this->builder
            ->description($values['description'])
            ->name($values['name'])
            ->type($values['type'])
            ->optional($values['optional'])
            ->defaultValue($values['defaultValue'])
            ->build(new ServiceDefinitionBuilder())
        ;

        $this->assertSame($values['description'], $definition->getDescription());
        $this->assertSame($values['name'], $definition->getName());
        $this->assertSame($values['type'], $definition->getType()->getName());
        $this->assertSame($values['optional'], $definition->isOptional());
        $this->assertSame($values['defaultValue'], $definition->getDefaultValue());
    }

    /**
     * @return array
     */
    public function settersProvider()
    {
        return [
            [
                [
                    'description' => 'Description 1',
                    'name' => 'name1',
                    'type' => 'int',
                    'optional' => true,
                    'defaultValue' => 5,
                ]
            ],
            [
                [
                    'description' => 'Description 2',
                    'name' => 'name2',
                    'type' => 'boolean',
                    'optional' => false,
                    'defaultValue' => null,
                ]
            ]
        ];
    }

    /**
     *
     */
    public function test_build_without_type()
    {
        $this->expectException('Bdf\Api\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The definition type "" is not defined');

        $this->builder->build(new ServiceDefinitionBuilder());
    }

    /**
     *
     */
    public function test_build_with_unknown_type()
    {
        $this->expectException('Bdf\Api\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The definition type "unknown" is not defined');

        $this->builder->type('unknown');

        $this->builder->build(new ServiceDefinitionBuilder());
    }

    /**
     *
     */
    public function test_build_with_name()
    {
        $this->builder
            ->name('param1')
            ->type('double')
        ;

        $this->assertEquals(
            (new ParameterDefinition('param1', new SimpleTypeDefinition('double'))),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }

    /**
     *
     */
    public function test_build_with_description()
    {
        $this->builder
            ->type('double')
            ->description('Ma description')
        ;

        $this->assertEquals(
            (new ParameterDefinition('', new SimpleTypeDefinition('double')))->setDescription('Ma description'),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }

    /**
     *
     */
    public function test_build_with_defaultValue()
    {
        $this->builder
            ->type('double')
            ->defaultValue(5.5)
        ;

        $this->assertEquals(
            (new ParameterDefinition('', new SimpleTypeDefinition('double')))->setDefaultValue(5.5),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }

    /**
     *
     */
    public function test_build_with_optional()
    {
        $this->builder
            ->type('double')
            ->optional(true)
        ;

        $this->assertEquals(
            (new ParameterDefinition('', new SimpleTypeDefinition('double')))->setOptional(true),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }

    /**
     *
     */
    public function test_build_as_array()
    {
        $this->builder
            ->name('param1')
            ->type('string')
            ->collection()
        ;

        $this->assertEquals(
            (new ParameterDefinition('param1', new ArrayTypeDefinition(new SimpleTypeDefinition('string')))),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }
}
