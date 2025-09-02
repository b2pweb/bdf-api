<?php

namespace Bdf\Api\Mapping\Builder;

use Bdf\Api\Mapping\Metadata\Types\EmbeddedPropertyMetadata;
use Bdf\Api\Mapping\Metadata\Types\SimpleTypeMetadata;
use Bdf\Api\Mapping\Registry;
use PHPUnit\Framework\TestCase;

/**
 * Class EmbeddedTypePropertyBuilderTest
 *
 * @package Bdf\Api\Mapping\Builder
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Builder
 * @group Bdf_Api_Mapping_Builder_EmbeddedTypePropertyBuilder
 */
class EmbeddedTypePropertyBuilderTest extends TestCase
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var EmbeddedTypePropertyBuilder
     */
    protected $builder;

    /**
     * @var ServiceBuilder
     */
    protected $serviceBuilder;


    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->registry = $this->createMock(Registry::class);
        $this->builder = new EmbeddedTypePropertyBuilder($this->registry);
        $this->serviceBuilder = $this->createMock(ServiceBuilder::class);
    }

    /**
     *
     */
    public function test_setters()
    {
        $service = new ServiceBuilder();
        $service->type('Location', \stdClass::class);

        $this->builder
            ->name('property1')
            ->type('Location')
        ;

        $metadata = $this->builder->build($service);

        $this->assertSame('property1', $metadata->getName());
        $this->assertSame('Location', $metadata->getType()->getName());
    }

    /**
     *
     */
    public function test_properties()
    {
        $service = new ServiceBuilder();
        $service->type('Location', \stdClass::class);

        $mapping = [
            'fromCity' => 'city',
            'fromCountry' => 'country'
        ];

        $this->builder->type('Location');
        $this->builder->properties($mapping);

        $metadata = $this->builder->build($service);

        $this->assertSame($mapping, $metadata->getPropertyMapping());
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

        $this->builder->filter('filter3', false);

        $this->assertEquals(
            ['filter3', 'filter1', 'filter2'],
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
    public function test_transformer()
    {
        $this->builder->transformer('Transformer1');

        $this->assertEquals(
            'Transformer1',
            $this->getAttributeValue($this->builder, 'transformer')
        );
    }

    /**
     *
     */
    public function test_build_with_name()
    {
        $this->serviceBuilder->expects($this->once())->method('buildType')
            ->with('int')
            ->will($this->returnValue(new SimpleTypeMetadata('int')))
        ;

        $this->builder->name('MyName');
        $this->builder->type('int');

        $this->assertEquals(
            new EmbeddedPropertyMetadata('MyName', new SimpleTypeMetadata('int')),
            $this->builder->build($this->serviceBuilder)
        );
    }

    /**
     *
     */
    public function test_build_with_propertiesMapping()
    {
        $this->serviceBuilder->expects($this->once())->method('buildType')
            ->with('int')
            ->will($this->returnValue(new SimpleTypeMetadata('int')))
        ;

        $this->builder->type('int');
        $this->builder->properties([
            'fromCity' => 'city',
            'fromCountry' => 'country'
        ]);

        $this->assertEquals(
            (new EmbeddedPropertyMetadata(null, new SimpleTypeMetadata('int')))
                ->setPropertyMapping([
                    'fromCity' => 'city',
                    'fromCountry' => 'country'
                ]),
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
            (new EmbeddedPropertyMetadata(null, new SimpleTypeMetadata('int')))
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
            (new EmbeddedPropertyMetadata(null, new SimpleTypeMetadata('int')))
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
            (new EmbeddedPropertyMetadata(null, new SimpleTypeMetadata('int')))
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
