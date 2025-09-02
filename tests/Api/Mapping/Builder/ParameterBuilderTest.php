<?php

namespace Bdf\Api\Mapping\Builder;

use Bdf\Api\Mapping\Metadata\ParameterMetadata;
use Bdf\Api\Mapping\Metadata\Types\SimpleTypeMetadata;
use Bdf\Api\Mapping\Registry;
use Bdf\Api\Mapping\Transformers\TransformerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ParameterBuilderTest
 *
 * @package Bdf\Api\Mapping\Builder
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Builder
 * @group Bdf_Api_Mapping_Builder_ParameterBuilder
 */
class ParameterBuilderTest extends TestCase
{
    /**
     * @var ParameterBuilder
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
        $this->builder = new ParameterBuilder($this->registry);
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

        $metadata = $this->builder
            ->name('parameter')
            ->transformer('Test')
            ->type('int[]')
            ->build(new ServiceBuilder())
        ;

        $this->assertEquals('parameter', $metadata->getName());
        $this->assertEquals('int[]', $metadata->getType()->getName());
        $this->assertEquals($transformer, $metadata->getTransformer());
    }

    /**
     *
     */
    public function test_filter()
    {
        $this->builder->filter('filter1');
        $this->builder->filter('filter2');

        $this->assertEquals(
            ['filter1', 'filter2'],
            $this->getAttributeValue($this->builder, 'filters')
        );
    }

    /**
     *
     */
    public function test_constraint()
    {
        $this->builder->constraint('constraint1');
        $this->builder->constraint('constraint2');
        $this->builder->constraint('constraint3');

        $this->assertEquals(
            ['constraint1', 'constraint2', 'constraint3'],
            $this->getAttributeValue($this->builder, 'constraints')
        );
    }

    /**
     *
     */
    public function test_build_with_name()
    {
        $this->serviceBuilder->expects($this->at(0))->method('buildType')
            ->with('int')
            ->will($this->returnValue(new SimpleTypeMetadata('int')))
        ;

        $this->builder->name('value');
        $this->builder->type('int');

        $this->assertEquals(
            new ParameterMetadata('value', new SimpleTypeMetadata('int')),
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

        $this->serviceBuilder->expects($this->at(0))->method('buildType')
            ->with('int')
            ->will($this->returnValue(new SimpleTypeMetadata('int')))
        ;

        $this->registry->expects($this->at(0))->method('getFilter')
            ->with('filter1')
            ->will($this->returnValue($filter1))
        ;

        $this->registry->expects($this->at(1))->method('getFilter')
            ->with('filter2')
            ->will($this->returnValue($filter2))
        ;

        $this->builder->type('int');
        $this->builder->filter('filter1');
        $this->builder->filter('filter2');

        $this->assertEquals(
            (new ParameterMetadata(null, new SimpleTypeMetadata('int')))
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

        $this->serviceBuilder->expects($this->at(0))->method('buildType')
            ->with('int')
            ->will($this->returnValue(new SimpleTypeMetadata('int')))
        ;

        $this->registry->expects($this->at(0))->method('getTransformer')
            ->with('transformer')
            ->will($this->returnValue($transformer))
        ;

        $this->builder->type('int');
        $this->builder->transformer('transformer');

        $this->assertEquals(
            (new ParameterMetadata(null, new SimpleTypeMetadata('int')))
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

        $this->serviceBuilder->expects($this->at(0))->method('buildType')
            ->with('int')
            ->will($this->returnValue(new SimpleTypeMetadata('int')))
        ;

        $this->registry->expects($this->at(0))->method('getConstraint')
            ->with('constraint1')
            ->will($this->returnValue($constraint1))
        ;

        $this->registry->expects($this->at(1))->method('getConstraint')
            ->with('constraint2')
            ->will($this->returnValue($constraint2))
        ;

        $this->builder->type('int');
        $this->builder->constraint('constraint1');
        $this->builder->constraint('constraint2');

        $this->assertEquals(
            (new ParameterMetadata(null, new SimpleTypeMetadata('int')))
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
