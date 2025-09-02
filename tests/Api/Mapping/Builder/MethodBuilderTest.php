<?php

namespace Bdf\Api\Mapping\Builder;

use Bdf\Api\Mapping\Metadata\MethodMetadata;
use Bdf\Api\Mapping\Metadata\ParameterMetadata;
use Bdf\Api\Mapping\Metadata\ReturnMetadata;
use Bdf\Api\Mapping\Metadata\Types\OptionTypeMetadata;
use Bdf\Api\Mapping\Metadata\Types\SimpleTypeMetadata;
use Bdf\Api\Mapping\Registry;
use PHPUnit\Framework\TestCase;

/**
 * Class MethodBuilderTest
 *
 * @package Bdf\Api\Mapping\Builder
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Builder
 * @group Bdf_Api_Mapping_Builder_MethodBuilder
 */
class MethodBuilderTest extends TestCase
{
    /**
     * @var MethodBuilder
     */
    protected $builder;

    /**
     * @var ServiceBuilder
     */
    protected $serviceBuilder;

    /**
     * @var Registry
     */
    protected $registry;


    public function setUp(): void
    {
        parent::setUp();

        $this->registry = $this->createMock(Registry::class);
        $this->builder = new MethodBuilder($this->registry);
        $this->serviceBuilder = $this->createMock(ServiceBuilder::class);
    }

    /**
     *
     */
    public function test_setters()
    {
        $metadata = $this->builder->name('method1')->build(new ServiceBuilder());

        $this->assertInstanceOf(MethodMetadata::class, $metadata);
        $this->assertSame('method1', $metadata->getName());
    }

    /**
     *
     */
    public function test_returns()
    {
        $metadata = $this->builder->returns('string')->build(new ServiceBuilder());

        $this->assertSame('string', $metadata->getType()->getName());
    }

    /**
     *
     */
    public function test_parameter()
    {
        $this->builder->parameter('param1', 'int');
        $this->builder->parameter('param2', 'string');
        $metadata = $this->builder->build(new ServiceBuilder());

        $this->assertSame('int', $metadata->getParameter('param1')->getType()->getName());
        $this->assertSame('string', $metadata->getParameter('param2')->getType()->getName());
    }

    /**
     *
     */
    public function test_array_parameter()
    {
        $this->builder->simpleArray('param1');
        $this->builder->string('param2')->collection();
        $this->builder->parameter('param3', 'string[]');

        $metadata = $this->builder->build(new ServiceBuilder());

        $this->assertSame('array', $metadata->getParameter('param1')->getType()->getName());
        $this->assertSame('array', $metadata->getParameter('param2')->getType()->getName());
        $this->assertSame('string[]', $metadata->getParameter('param3')->getType()->getName());
    }

    /**
     * @dataProvider parameterAddingMethodsProvider
     *
     * @param string $method
     * @param string $type
     */
    public function test_parameterMethods($method, $type)
    {
        /** @var ParameterMetadata $metadata */
        $metadata = $this->builder->$method('parameter')->build(new ServiceBuilder());

        $this->assertSame('parameter', $metadata->getName());
        $this->assertSame($type, $metadata->getType()->getName());
    }

    /**
     * @return array
     */
    public function parameterAddingMethodsProvider()
    {
        return [
            // [méthode, type]
            ['object', 'object'],
            ['mixed', 'mixed'],
            ['string', 'string'],
            ['integer', 'integer'],
            ['long', 'long'],
            ['float', 'float'],
            ['double', 'double'],
            ['boolean', 'boolean'],
            ['simpleArray', 'array'],
            ['options', 'options'],
        ];
    }

    /**
     *
     */
    public function test_constraint()
    {
        $this->builder->constraint('constraint1');
        $this->builder->constraint('constraint2');

        $this->assertEquals(
            ['constraint1', 'constraint2'],
            $this->getAttributeValue($this->builder, 'constraints')
        );

        $this->builder->constraint('constraint3', false);

        $this->assertEquals(
            ['constraint3', 'constraint1', 'constraint2'],
            $this->getAttributeValue($this->builder, 'constraints')
        );
    }

    /**
     *
     */
    public function test_build_with_parameters()
    {
        $this->serviceBuilder->expects($this->at(0))->method('buildType')
            ->with('int')
            ->will($this->returnValue(new SimpleTypeMetadata('int')))
        ;

        $this->serviceBuilder->expects($this->at(1))->method('buildType')
            ->with('options')
            ->will($this->returnValue(new OptionTypeMetadata()))
        ;

        $this->builder->parameter('param1', 'int');
        $this->builder->parameter('param2', 'options');

        $this->assertEquals(
            (new MethodMetadata(null))
                ->addParameter(new ParameterMetadata('param1', new SimpleTypeMetadata('int')))
                ->addParameter(new ParameterMetadata('param2', new OptionTypeMetadata()))
                ->setParameterOption('param2'),
            $this->builder->build($this->serviceBuilder)
        );
    }

    /**
     *
     */
    public function test_build_with_return()
    {
        $this->serviceBuilder->expects($this->at(0))->method('buildType')
            ->with('boolean')
            ->will($this->returnValue(new SimpleTypeMetadata('boolean')))
        ;

        $this->builder->returns('boolean');

        $this->assertEquals(
            (new MethodMetadata(null))
                ->setReturn(new ReturnMetadata(new SimpleTypeMetadata('boolean'))),
            $this->builder->build($this->serviceBuilder)
        );
    }

    /**
     *
     */
    public function test_build_with_constraints()
    {
        $constraint1 = $this->createMock('Bdf\Api\Mapping\Constraints\ConstraintInterface');
        $constraint2 = $this->createMock('Bdf\Api\Mapping\Constraints\ConstraintInterface');

        $this->registry->expects($this->at(0))->method('getConstraint')
            ->with('constraint1')
            ->will($this->returnValue($constraint1))
        ;

        $this->registry->expects($this->at(1))->method('getConstraint')
            ->with('constraint2')
            ->will($this->returnValue($constraint2))
        ;

        $this->builder->constraint('constraint1');
        $this->builder->constraint('constraint2');

        $this->assertEquals(
            (new MethodMetadata(null))
                ->addConstraint($constraint1)
                ->addConstraint($constraint2),
            $this->builder->build($this->serviceBuilder)
        );
    }

    private function getAttributeValue(object $object, string $property): mixed
    {
        $fn = fn () => $object->$property;
        $fn = $fn->bindTo(null, $object);

        return $fn();
    }
}
