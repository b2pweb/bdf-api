<?php

namespace Bdf\Api\Definition\Builder;

use Bdf\Api\Definition\ArrayTypeDefinition;
use Bdf\Api\Definition\PropertyDefinition;
use Bdf\Api\Definition\SimpleTypeDefinition;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

/**
 * Class PropertyDefinitionBuilderTest
 *
 * @package Bdf\Api\Definition\Builder
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Definition
 * @group Bdf_Api_Definition_Builder
 * @group Bdf_Api_Definition_Builder_PropertyDefinitionBuilder
 *
 * @coversDefaultClass Bdf\Api\Definition\Builder\PropertyDefinitionBuilder
 */
class PropertyDefinitionBuilderTest extends TestCase
{
    /**
     * @var PropertyDefinitionBuilder
     */
    protected $builder;


    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->builder = new PropertyDefinitionBuilder();
    }

    /**
     * @dataProvider settersProvider
     *
     * @param array $values
     */
    public function test_setters(array $values)
    {
        $definition = $this->builder
            ->optional($values['optional'])
            ->nillable($values['nillable'])
            ->name($values['name'])
            ->type($values['type'])
            ->build(new ServiceDefinitionBuilder())
        ;

        $this->assertSame($values['optional'], $definition->isOptional());
        $this->assertSame($values['nillable'], $definition->isNillable());
        $this->assertSame($values['name'], $definition->getName());
        $this->assertSame($values['type'], $definition->getType()->getName());
    }

    /**
     * @return array
     */
    public function settersProvider()
    {
        return [
            [
                [
                    'optional' => false,
                    'nillable' => true,
                    'name' => 'name1',
                    'type' => 'boolean',
                ]
            ],
            [
                [
                    'optional' => true,
                    'nillable' => false,
                    'name' => 'name2',
                    'type' => 'string',
                ]
            ],
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
            ->name('property1')
            ->type('int')
        ;

        $this->assertEquals(
            (new PropertyDefinition('property1', new SimpleTypeDefinition('int')))->setNillable(true)->setOptional(false),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }

    /**
     *
     */
    public function test_build_with_optional()
    {
        $this->builder
            ->optional(true)
            ->type('int')
        ;

        $this->assertEquals(
            (new PropertyDefinition('', new SimpleTypeDefinition('int')))->setNillable(true)->setOptional(true),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }

    /**
     *
     */
    public function test_build_with_nillable()
    {
        $this->builder
            ->nillable(false)
            ->type('int')
        ;

        $this->assertEquals(
            (new PropertyDefinition('', new SimpleTypeDefinition('int')))->setNillable(false)->setOptional(false),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }

    /**
     *
     */
    public function test_build_as_array()
    {
        $this->builder
            ->name('property1')
            ->type('string')
            ->collection()
        ;

        $this->assertEquals(
            (new PropertyDefinition('property1', new ArrayTypeDefinition(new SimpleTypeDefinition('string'))))->setNillable(true)->setOptional(false),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }
}
