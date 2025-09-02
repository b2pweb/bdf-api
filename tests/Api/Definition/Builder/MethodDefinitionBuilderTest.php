<?php

namespace Bdf\Api\Definition\Builder;

use Bdf\Api\Definition\ArrayTypeDefinition;
use Bdf\Api\Definition\MethodDefinition;
use Bdf\Api\Definition\ParameterDefinition;
use Bdf\Api\Definition\SimpleTypeDefinition;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

/**
 * Class MethodDefinitionBuilderTest
 *
 * @package Bdf\Api\Definition\Builder
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Definition
 * @group Bdf_Api_Definition_Builder
 * @group Bdf_Api_Definition_Builder_MethodDefinitionBuilder
 *
 * @coversDefaultClass Bdf\Api\Definition\Builder\MethodDefinitionBuilder
 */
class MethodDefinitionBuilderTest extends TestCase
{
    /**
     * @var MethodDefinitionBuilder
     */
    protected $builder;


    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->builder = new MethodDefinitionBuilder();
    }

    /**
     * @dataProvider settersProvider
     *
     * @param array $values
     */
    public function test_setters(array $values)
    {
        $definition = $this->builder
            ->name($values['name'])
            ->description($values['description'])
            ->returns($values['returnType'])
            ->throwingException($values['throwingException'])
            ->build(new ServiceDefinitionBuilder())
        ;

        $this->assertSame($values['name'], $definition->getName());
        $this->assertSame($values['description'], $definition->getDescription());
        $this->assertSame($values['returnType'], $definition->getReturn() ? $definition->getReturn()->getName() : null);
        $this->assertSame($values['throwingException'], $definition->isThrowingException());
    }

    /**
     * @return array
     */
    public function settersProvider()
    {
        return [
            [
                [
                    'name' => 'method1',
                    'description' => 'Ma methode 1',
                    'returnType' => 'double',
                    'throwingException' => false,
                ]
            ],
            [
                [
                    'name' => 'method2',
                    'description' => 'Ma methode 2',
                    'returnType' => null,
                    'throwingException' => true,
                ]
            ]
        ];
    }

    /**
     *
     */
    public function test_parameter()
    {
        $definition = $this->builder->parameter('parameter', 'string')
            ->build(new ServiceDefinitionBuilder());

        $this->assertSame('parameter', $definition->getName());
        $this->assertSame('string', $definition->getType()->getName());
        $this->assertSame(null, $definition->getDefaultValue());
    }

    /**
     *
     */
    public function test_parameter_with_defaultValue()
    {
        $definition = $this->builder->parameter('parameter', 'string', 'bonjour')
            ->build(new ServiceDefinitionBuilder());

        $this->assertSame('parameter', $definition->getName());
        $this->assertSame('string', $definition->getType()->getName());
        $this->assertSame('bonjour', $definition->getDefaultValue());
    }

    /**
     *
     */
    public function test_multiple_add()
    {
        $service = new ServiceDefinitionBuilder();

        $this->builder->object('param1');
        $this->builder->mixed('param2');
        $this->builder->string('param3');
        $this->builder->integer('param4');
        $this->builder->long('param5');
        $this->builder->float('param6');
        $this->builder->double('param7');
        $this->builder->boolean('param8');
        $this->builder->rpcArray('param9');
        $this->builder->string('param10')->collection();
        $this->builder->parameter('param11', 'string[]')->collection();

        $parameters = $this->builder->build($service)->getParameters();

        $this->assertCount(11, $parameters);
        $this->assertSame('param1', $parameters[0]->getName());
        $this->assertSame('object', $parameters[0]->getType()->getName());
        $this->assertSame(0, $parameters[0]->getPosition());

        $this->assertSame('param2', $parameters[1]->getName());
        $this->assertSame('mixed', $parameters[1]->getType()->getName());
        $this->assertSame(1, $parameters[1]->getPosition());

        $this->assertSame('param3', $parameters[2]->getName());
        $this->assertSame('string', $parameters[2]->getType()->getName());
        $this->assertSame(2, $parameters[2]->getPosition());

        $this->assertSame('param4', $parameters[3]->getName());
        $this->assertSame('integer', $parameters[3]->getType()->getName());
        $this->assertSame(3, $parameters[3]->getPosition());

        $this->assertSame('param5', $parameters[4]->getName());
        $this->assertSame('long', $parameters[4]->getType()->getName());
        $this->assertSame(4, $parameters[4]->getPosition());

        $this->assertSame('param6', $parameters[5]->getName());
        $this->assertSame('float', $parameters[5]->getType()->getName());
        $this->assertSame(5, $parameters[5]->getPosition());

        $this->assertSame('param7', $parameters[6]->getName());
        $this->assertSame('double', $parameters[6]->getType()->getName());
        $this->assertSame(6, $parameters[6]->getPosition());

        $this->assertSame('param8', $parameters[7]->getName());
        $this->assertSame('boolean', $parameters[7]->getType()->getName());
        $this->assertSame(7, $parameters[7]->getPosition());

        $this->assertSame('param9', $parameters[8]->getName());
        $this->assertSame('array', $parameters[8]->getType()->getName());
        $this->assertSame(8, $parameters[8]->getPosition());

        $this->assertSame('param10', $parameters[9]->getName());
        $this->assertSame('string[]', $parameters[9]->getType()->getName());
        $this->assertSame(9, $parameters[9]->getPosition());

        $this->assertSame('param11', $parameters[10]->getName());
        $this->assertSame('string[]', $parameters[10]->getType()->getName());
        $this->assertSame(10, $parameters[10]->getPosition());
    }

    /**
     * @dataProvider parameterAddingMethodsProvider
     *
     * @param string $method
     * @param string $type
     * @param mixed $defaultValue
     */
    public function test_parameterMethods($method, $type, $defaultValue)
    {
        $service = new ServiceDefinitionBuilder();

        /** @var ParameterDefinition $definition */
        $definition = $this->builder->$method('parameter', $defaultValue)->build($service);

        $this->assertEquals('parameter', $definition->getName());
        $this->assertEquals($type, $definition->getType()->getName());
        $this->assertEquals($defaultValue, $definition->getDefaultValue());
    }

    /**
     * @return array
     */
    public function parameterAddingMethodsProvider()
    {
        return [
            // [méthode, type, defaultValue]
            ['object', 'object', null],
            ['mixed', 'mixed', null],
            ['string', 'string', null],
            ['string', 'string', 'test'],
            ['integer', 'integer', null],
            ['integer', 'integer', 5],
            ['long', 'long', null],
            ['long', 'long', 10],
            ['float', 'float', null],
            ['float', 'float', 6.6],
            ['double', 'double', null],
            ['double', 'double', 10.0],
            ['boolean', 'boolean', null],
            ['boolean', 'boolean', true],
            ['rpcArray', 'array', null],
            ['rpcArray', 'array', [2, 3, 4]],
        ];
    }

    /**
     *
     */
    public function test_build_with_name()
    {
        $this->builder->name('method1');

        $this->assertEquals(
            new MethodDefinition('method1'),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }

    /**
     *
     */
    public function test_build_with_description()
    {
        $this->builder->description('ma description');

        $this->assertEquals(
            (new MethodDefinition(''))->setDescription('ma description'),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }

    /**
     *
     */
    public function test_build_with_returns()
    {
        $this->builder->returns('int');

        $this->assertEquals(
            (new MethodDefinition(''))->setReturn(new SimpleTypeDefinition('int')),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }

    /**
     *
     */
    public function test_build_with_wrong_returns()
    {
        $this->expectException('Bdf\Api\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The definition type "unknown" is not defined');

        $this->builder->returns('unknown');

        $this->builder->build(new ServiceDefinitionBuilder());
    }

    /**
     *
     */
    public function test_build_with_throwingException()
    {
        $this->builder->throwingException(true);

        $this->assertEquals(
            (new MethodDefinition(''))->setThrowingException(true),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }

    /**
     *
     */
    public function test_build_with_parameters()
    {
        $this->builder->integer('param1');
        $this->builder->boolean('param2');
        $this->builder->string('param3')->collection();

        $this->assertEquals(
            (new MethodDefinition(''))
                ->addParameter(new ParameterDefinition('param1', new SimpleTypeDefinition('integer')))
                ->addParameter(new ParameterDefinition('param2', new SimpleTypeDefinition('boolean')))
                ->addParameter(new ParameterDefinition('param3', new ArrayTypeDefinition(new SimpleTypeDefinition('string')))),
            $this->builder->build(new ServiceDefinitionBuilder())
        );
    }
}
