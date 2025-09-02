<?php

namespace Bdf\Api\Definition\Builder;

use Bdf\Api\Definition\ArrayTypeDefinition;
use Bdf\Api\Definition\ComplexTypeDefinition;
use Bdf\Api\Definition\MethodDefinition;
use Bdf\Api\Definition\ParameterDefinition;
use Bdf\Api\Definition\PropertyDefinition;
use Bdf\Api\Definition\ServiceDefinition;
use Bdf\Api\Definition\SimpleTypeDefinition;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

/**
 * Class ServiceDefinitionBuilderTest
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Definition
 * @group Bdf_Api_Definition_Builder
 * @group Bdf_Api_Definition_Builder_ServiceDefinitionBuilder
 *
 * @package Bdf\Api\Definition\Builder
 *
 * @coversDefaultClass Bdf\Api\Definition\Builder\ServiceDefinitionBuilder
 */
class ServiceDefinitionBuilderTest extends TestCase
{
    /**
     * @var ServiceDefinitionBuilder
     */
    protected $builder;


    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new ServiceDefinitionBuilder();
    }

    /**
     *
     */
    public function test_name()
    {
        $definition = $this->builder->name('MonService')->build();

        $this->assertEquals('MonService', $definition->getName());
    }

    /**
     *
     */
    public function test_namespace()
    {
        $definition = $this->builder->namespace('http://ny-ns.example.com')->build();

        $this->assertEquals('http://ny-ns.example.com', $definition->getNamespace());
    }

    /**
     *
     */
    public function test_method()
    {
        $definition = $this->builder->method('method')->build();

        $this->assertInstanceOf(MethodDefinition::class, $definition->getMethod('method'));
        $this->assertSame('method', $definition->getMethod('method')->getName());
    }

    /**
     *
     */
    public function test_method_with_initializer()
    {
        $this->builder->method('method', function(MethodDefinitionBuilder $builder) {
            $builder->string('param1');
        });
        $definition = $this->builder->build();

        $this->assertCount(1, $definition->getMethods());
        $this->assertCount(1, $definition->getMethod('method')->getParameters());

        $parameter = $definition->getMethod('method')->getParameters()[0];
        $this->assertSame('param1', $parameter->getName());
        $this->assertSame('string', $parameter->getType()->getName());
    }

    /**
     *
     */
    public function test_multiple_method()
    {
        $this->builder->method('method1');
        $this->builder->method('method2');

        $definition = $this->builder->build();

        $this->assertCount(2, $definition->getMethods());
        $this->assertInstanceOf(MethodDefinition::class, $definition->getMethod('method1'));
        $this->assertInstanceOf(MethodDefinition::class, $definition->getMethod('method2'));
    }

    /**
     *
     */
    public function test_complexType()
    {
        $this->builder->complexType('type');

        $definition = $this->builder->build();

        $this->assertInstanceOf(ComplexTypeDefinition::class, $definition->getType('type'));
    }

    /**
     *
     */
    public function test_complexType_with_initializer()
    {
        $this->builder->complexType('type', function(ComplexTypeDefinitionBuilder $builder) {
            $builder->string('prop1');
        });

        $definition = $this->builder->build();

        $this->assertSame('string', $definition->getType('type')->getProperty('prop1')->getType()->getName());
    }

    /**
     *
     */
    public function test_multiple_complexType()
    {
        $this->builder->complexType('type1');
        $this->builder->complexType('type2');

        $definition = $this->builder->build();

        $this->assertCount(2, $definition->getTypes());
        $this->assertInstanceOf(ComplexTypeDefinition::class, $definition->getType('type1'));
        $this->assertInstanceOf(ComplexTypeDefinition::class, $definition->getType('type2'));
    }

    /**
     *
     */
    public function test_build_with_name()
    {
        $this->builder
            ->name('Test')
        ;

        $this->assertEquals(
            new ServiceDefinition('Test'),
            $this->builder->build()
        );
    }

    /**
     *
     */
    public function test_build_with_methods()
    {
        $this->builder
            ->method('method1', function (MethodDefinitionBuilder $builder) {
                $builder->parameter('param1', 'string');
            })
            ->method('method2', function (MethodDefinitionBuilder $builder) {
                $builder->parameter('param1', 'int');
                $builder->parameter('param2', 'string');
            })
        ;

        $this->assertEquals(
            (new ServiceDefinition(''))
                ->addMethod(
                    (new MethodDefinition('method1'))
                        ->addParameter(new ParameterDefinition('param1', new SimpleTypeDefinition('string')))
                )
                ->addMethod(
                    (new MethodDefinition('method2'))
                        ->addParameter(new ParameterDefinition('param1', new SimpleTypeDefinition('int')))
                        ->addParameter(new ParameterDefinition('param2', new SimpleTypeDefinition('string')))
                ),
            $this->builder->build()
        );
    }

    /**
     *
     */
    public function test_build_with_types()
    {
        $this->builder
            ->complexType('Type1', function (ComplexTypeDefinitionBuilder $builder) {
                $builder->property('name', 'string');
            })
            ->complexType('Type2', function (ComplexTypeDefinitionBuilder $builder) {
                $builder->property('city', 'string');
                $builder->property('country', 'string');
            })
        ;

        $this->assertEquals(
            (new ServiceDefinition(''))
                ->addType(new ComplexTypeDefinition('Type1', [
                    (new PropertyDefinition('name', new SimpleTypeDefinition('string')))->setNillable(true)->setOptional(false),
                ]))
                ->addType(new ComplexTypeDefinition('Type2', [
                    (new PropertyDefinition('city', new SimpleTypeDefinition('string')))->setNillable(true)->setOptional(false),
                    (new PropertyDefinition('country', new SimpleTypeDefinition('string')))->setNillable(true)->setOptional(false),
                ])),
            $this->builder->build()
        );
    }


    /**
     *
     */
    public function test_build_with_types_and_methods()
    {
        $this->builder
            ->method('method1', function (MethodDefinitionBuilder $builder) {
                $builder->parameter('param1', 'Type1');
            })
            ->method('method2', function (MethodDefinitionBuilder $builder) {
                $builder->parameter('param1', 'Type2');
                $builder->parameter('param2', 'string');
            })
            ->complexType('Type1', function (ComplexTypeDefinitionBuilder $builder) {
                $builder->property('name', 'string');
            })
            ->complexType('Type2', function (ComplexTypeDefinitionBuilder $builder) {
                $builder->property('city', 'string');
                $builder->property('country', 'string');
            })
        ;

        $this->assertEquals(
            (new ServiceDefinition(''))
                ->setMethods([
                    (new MethodDefinition('method1'))
                        ->addParameter(new ParameterDefinition('param1', $this->getType1Definition())),
                    (new MethodDefinition('method2'))
                        ->addParameter(new ParameterDefinition('param1', $this->getType2Definition()))
                        ->addParameter(new ParameterDefinition('param2', new SimpleTypeDefinition('string')))
                ])
                ->setTypes([
                    'Type1' => $this->getType1Definition(),
                    'Type2' => $this->getType2Definition(),
                ]),
            $this->builder->build()
        );
    }

    /**
     *
     */
    public function test_buildType_with_complexType()
    {
        $this->builder->complexType('Offer');

        $this->assertEquals(
            new ComplexTypeDefinition('Offer'),
            $this->builder->buildType('Offer')
        );
    }

    /**
     *
     */
    public function test_buildType_with_simpleType()
    {
        $this->assertEquals(
            new SimpleTypeDefinition('int'),
            $this->builder->buildType('int')
        );
    }

    /**
     *
     */
    public function test_buildType_with_arrayType()
    {
        $this->assertEquals(
            new ArrayTypeDefinition(new SimpleTypeDefinition('string')),
            $this->builder->buildType('string[]')
        );
    }

    /**
     *
     */
    public function test_buildType_throws_exception_on_unknown_type()
    {
        $this->expectException('Bdf\Api\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The definition type "UnknownType" is not defined');

        $this->builder->buildType('UnknownType');
    }

    /**
     * @return ComplexTypeDefinition
     */
    protected function getType1Definition()
    {
        return new ComplexTypeDefinition('Type1', [
            (new PropertyDefinition('name', new SimpleTypeDefinition('string')))->setNillable(true)
        ]);
    }

    /**
     * @return ComplexTypeDefinition
     */
    protected function getType2Definition()
    {
        return new ComplexTypeDefinition('Type2', [
            (new PropertyDefinition('city', new SimpleTypeDefinition('string')))->setNillable(true),
            (new PropertyDefinition('country', new SimpleTypeDefinition('string')))->setNillable(true)
        ]);
    }
}
