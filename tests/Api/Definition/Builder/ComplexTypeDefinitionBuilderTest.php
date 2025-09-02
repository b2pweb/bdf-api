<?php

namespace Bdf\Api\Definition\Builder;

use Bdf\Api\Definition\ArrayTypeDefinition;
use Bdf\Api\Definition\ComplexTypeDefinition;
use Bdf\Api\Definition\PropertyDefinition;
use Bdf\Api\Definition\SimpleTypeDefinition;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

/**
 * Class ComplexTypeDefinitionBuilderTest
 *
 * @package Bdf\Api\Definition\Builder
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Definition
 * @group Bdf_Api_Definition_Builder
 * @group Bdf_Api_Definition_Builder_ComplexTypeDefinitionBuilder
 *
 * @coversDefaultClass Bdf\Api\Definition\Builder\ComplexTypeDefinitionBuilder
 */
class ComplexTypeDefinitionBuilderTest extends TestCase
{
    /**
     * @var ComplexTypeDefinitionBuilder
     */
    protected $builder;


    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->builder = new ComplexTypeDefinitionBuilder();
    }

    /**
     * @dataProvider settersProvider
     *
     * @param array $values
     */
    public function test_setters(array $values)
    {
        $service = new ServiceDefinitionBuilder();

        $this->builder
            ->name($values['name'])
            ->realName($values['realName'])
        ;

        $definition = $this->builder->build($service);

        $this->assertEquals($values['name'], $definition->getName());
        $this->assertEquals($values['realName'], $definition->getRealName());
    }

    /**
     * @return array
     */
    public function settersProvider()
    {
        return [
            [
                [
                    'name' => 'name1',
                    'realName' => 'realName1',
                ]
            ],
            [
                [
                    'name' => 'name2',
                    'realName' => null,
                ]
            ],
        ];
    }

    /**
     *
     */
    public function test_property()
    {
        $service = new ServiceDefinitionBuilder();

        $builder = $this->builder->property('property', 'string');

        $this->assertInstanceOf(PropertyDefinitionBuilder::class, $builder);
        $definition = $builder->build($service);

        $this->assertEquals('property', $definition->getName());
        $this->assertEquals('string', $definition->getType()->getName());
    }

    /**
     *
     */
    public function test_multiple_add()
    {
        $service = new ServiceDefinitionBuilder();

        $this->builder->object('property1');
        $this->builder->mixed('property2');
        $this->builder->string('property3');
        $this->builder->integer('property4');
        $this->builder->long('property5');
        $this->builder->float('property6');
        $this->builder->double('property7');
        $this->builder->boolean('property8');
        $this->builder->rpcArray('property9');
        $this->builder->string('property10')->collection();
        $this->builder->property('property11', 'string[]')->collection();

        $definition = $this->builder->build($service);

        $this->assertCount(11, $definition->getProperties());
        $this->assertEquals('object', $definition->getProperty('property1')->getType()->getName());
        $this->assertEquals('mixed', $definition->getProperty('property2')->getType()->getName());
        $this->assertEquals('string', $definition->getProperty('property3')->getType()->getName());
        $this->assertEquals('integer', $definition->getProperty('property4')->getType()->getName());
        $this->assertEquals('long', $definition->getProperty('property5')->getType()->getName());
        $this->assertEquals('float', $definition->getProperty('property6')->getType()->getName());
        $this->assertEquals('double', $definition->getProperty('property7')->getType()->getName());
        $this->assertEquals('boolean', $definition->getProperty('property8')->getType()->getName());
        $this->assertEquals('array', $definition->getProperty('property9')->getType()->getName());
        $this->assertEquals('string[]', $definition->getProperty('property10')->getType()->getName());
        $this->assertEquals('string[]', $definition->getProperty('property11')->getType()->getName());
    }

    /**
     * @dataProvider propertyProvider
     *
     * @param string $method
     * @param string $type
     */
    public function test_propertyMethods($method, $type)
    {
        $service = new ServiceDefinitionBuilder();

        $definition = $this->builder->$method('property')->build($service);

        $this->assertEquals('property', $definition->getName());
        $this->assertEquals($type, $definition->getType()->getName());
    }

    /**
     * @return array
     */
    public function propertyProvider()
    {
        return [
            ['object', 'object'],
            ['mixed', 'mixed'],
            ['string', 'string'],
            ['integer', 'integer'],
            ['long', 'long'],
            ['float', 'float'],
            ['double', 'double'],
            ['boolean', 'boolean'],
            ['rpcArray', 'array'],
        ];
    }

    /**
     *
     */
    public function test_build_empty()
    {
        $this->assertEquals(
            new ComplexTypeDefinition(''),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }

    /**
     *
     */
    public function test_build_with_name()
    {
        $this->builder->name('Test');

        $this->assertEquals(
            new ComplexTypeDefinition('Test'),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }

    /**
     *
     */
    public function test_build_with_realName()
    {
        $this->builder->realName('MyRealName');

        $this->assertEquals(
            (new ComplexTypeDefinition(''))->setRealName('MyRealName'),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }

    /**
     *
     */
    public function test_build_with_property()
    {
        $this->builder->string('property1');
        $this->builder->integer('property2');
        $this->builder->integer('property3')->collection();

        $this->assertEquals(
            (new ComplexTypeDefinition(''))
                ->addProperty(
                    (new PropertyDefinition('property1', new SimpleTypeDefinition('string')))->setNillable(true)
                )
                ->addProperty(
                    (new PropertyDefinition('property2', new SimpleTypeDefinition('integer')))->setNillable(true)
                )
                ->addProperty(
                    (new PropertyDefinition('property3', new ArrayTypeDefinition(new SimpleTypeDefinition('integer'))))->setNillable(true)
                ),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }
}
