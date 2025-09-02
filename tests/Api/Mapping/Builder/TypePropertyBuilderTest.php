<?php

namespace Bdf\Api\Mapping\Builder;

use Bdf\Api\Mapping\Metadata\Types\DefaultPropertyMetadata;
use Bdf\Api\Mapping\Metadata\Types\SimpleTypeMetadata;
use Bdf\Api\Mapping\Registry;
use Bdf\Api\Mapping\Transformers\TransformerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class TypePropertyBuilderTest
 *
 * @package Bdf\Api\Mapping\Builder
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Builder
 * @group Bdf_Api_Mapping_Builder_TypePropertyBuilder
 */
class TypePropertyBuilderTest extends TestCase
{
    /**
     * @var TypePropertyBuilder
     */
    protected $builder;

    /**
     * @var ServiceBuilder|MockObject
     */
    protected $serviceBuilder;

    /**
     * @var Registry|MockObject
     */
    protected $registry;


    public function setUp(): void
    {
        parent::setUp();

        $this->registry = $this->createMock(Registry::class);
        $this->builder = new TypePropertyBuilder($this->registry);
        $this->serviceBuilder = $this->createMock(ServiceBuilder::class);
    }

    /**
     *
     */
    public function test_setters()
    {
        $transformer = $this->createMock(TransformerInterface::class);

        $this->registry->expects($this->once())->method('getTransformer')
            ->with('Test')
            ->willReturn($transformer);

        $this->builder
            ->definedBy('prop')
            ->name('location')
            ->transformer('Test')
            ->type('string')
        ;

        $metadata = $this->builder->build(new ServiceBuilder());

        $this->assertEquals('location', $metadata->getName());
        $this->assertEquals('string', $metadata->getType()->getName());
        $this->assertEquals('prop', $metadata->getDefinedBy());
        $this->assertEquals($transformer, $metadata->getTransformer());
    }

    /**
     *
     */
    public function test_filter()
    {
        $this->builder
            ->filter('Filter1')
            ->filter('Filter2')
        ;

        $this->assertEquals(
            ['Filter1', 'Filter2'],
            $this->getAttributeValue($this->builder, 'filters')
        );

        $this->builder->filter('Filter3', false);

        $this->assertEquals(
            ['Filter3', 'Filter1', 'Filter2'],
            $this->getAttributeValue($this->builder, 'filters')
        );
    }

    /**
     *
     */
    public function test_constraint()
    {
        $this->builder
            ->constraint('Constraint1')
            ->constraint('Constraint2')
        ;

        $this->assertEquals(
            ['Constraint1', 'Constraint2'],
            $this->getAttributeValue($this->builder, 'constraints')
        );

        $this->builder->constraint('Constraint3', false);

        $this->assertEquals(
            ['Constraint3', 'Constraint1', 'Constraint2'],
            $this->getAttributeValue($this->builder, 'constraints')
        );
    }

    /**
     *
     */
    public function test_build()
    {
        $this->serviceBuilder->expects($this->once())->method('buildType')
            ->with('string')
            ->will($this->returnValue(new SimpleTypeMetadata('string')))
        ;

        $this->builder->name('property1');
        $this->builder->type('string');

        $this->assertEquals(
            new DefaultPropertyMetadata('property1', new SimpleTypeMetadata('string')),
            $this->builder->build($this->serviceBuilder)
        );
    }

    /**
     *
     */
    public function test_build_with_definedBy()
    {
        $this->serviceBuilder->expects($this->once())->method('buildType')
            ->with('string')
            ->will($this->returnValue(new SimpleTypeMetadata('string')))
        ;

        $this->builder->type('string');
        $this->builder->definedBy('test');

        $this->assertEquals(
            (new DefaultPropertyMetadata(null, new SimpleTypeMetadata('string')))
                ->setDefinedBy('test'),
            $this->builder->build($this->serviceBuilder)
        );
    }

    /**
     *
     */
    public function test_build_with_filters()
    {
        $filter1 = $this->createMock('Bdf\Api\Mapping\Filters\FilterInterface');
        $filter2 = $this->createMock('Bdf\Api\Mapping\Filters\FilterInterface');

        $this->serviceBuilder->expects($this->once())->method('buildType')
            ->with('string')
            ->will($this->returnValue(new SimpleTypeMetadata('string')))
        ;

        $this->registry->expects($this->at(0))->method('getFilter')
            ->with('filter1')
            ->will($this->returnValue($filter1))
        ;

        $this->registry->expects($this->at(1))->method('getFilter')
            ->with('filter2')
            ->will($this->returnValue($filter2))
        ;

        $this->builder->type('string');
        $this->builder->filter('filter1');
        $this->builder->filter('filter2');

        $this->assertEquals(
            (new DefaultPropertyMetadata(null, new SimpleTypeMetadata('string')))
                ->addFilter($filter1)
                ->addFilter($filter2),
            $this->builder->build($this->serviceBuilder)
        );
    }

    /**
     *
     */
    public function test_build_with_transformer()
    {
        $transformer = $this->createMock('Bdf\Api\Mapping\Transformers\TransformerInterface');

        $this->serviceBuilder->expects($this->once())->method('buildType')
            ->with('string')
            ->will($this->returnValue(new SimpleTypeMetadata('string')))
        ;

        $this->registry->expects($this->at(0))->method('getTransformer')
            ->with('transformer')
            ->will($this->returnValue($transformer))
        ;

        $this->builder->type('string');
        $this->builder->transformer('transformer');

        $this->assertEquals(
            (new DefaultPropertyMetadata(null, new SimpleTypeMetadata('string')))
                ->setTransformer($transformer),
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

        $this->serviceBuilder->expects($this->once())->method('buildType')
            ->with('string')
            ->will($this->returnValue(new SimpleTypeMetadata('string')))
        ;

        $this->registry->expects($this->at(0))->method('getConstraint')
            ->with('constraint1')
            ->will($this->returnValue($constraint1))
        ;

        $this->registry->expects($this->at(1))->method('getConstraint')
            ->with('constraint2')
            ->will($this->returnValue($constraint2))
        ;

        $this->builder->type('string');
        $this->builder->constraint('constraint1');
        $this->builder->constraint('constraint2');

        $this->assertEquals(
            (new DefaultPropertyMetadata(null, new SimpleTypeMetadata('string')))
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
