<?php

namespace Bdf\Api\Mapping\Builder;

use Bdf\Api\Mapping\Metadata\Types\ComplexTypeMetadata;
use Bdf\Api\Mapping\Metadata\Types\DefaultPropertyMetadata;
use Bdf\Api\Mapping\Metadata\Types\PropertyMetadata;
use Bdf\Api\Mapping\Metadata\Types\SimpleTypeMetadata;
use Bdf\Api\Mapping\Registry;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class TypeBuilderTest
 *
 * @package Bdf\Api\Mapping
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Builder
 * @group Bdf_Api_Mapping_Builder_TypeBuilder
 */
class TypeBuilderTest extends TestCase
{
    /**
     * @var TypeBuilder
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
        $this->builder = new TypeBuilder($this->registry);
        $this->serviceBuilder = $this->createMock(ServiceBuilder::class);
    }

    /**
     *
     */
    public function test_setters()
    {
        $this->builder->name('Type1');
        $this->builder->className('Namespace\Type1');
        $metadata = $this->builder->build(new ServiceBuilder());

        $this->assertEquals('Type1', $metadata->getName());
        $this->assertEquals('Namespace\Type1', $metadata->getClass());
    }

    /**
     *
     */
    public function test_property()
    {
        $this->assertInstanceOf(
            'Bdf\Api\Mapping\Builder\TypePropertyBuilder',
            $this->builder->property('property', 'string')
        );

        $this->assertEquals(
            [
                'property' => (new TypePropertyBuilder($this->registry))->name('property')->type('string')
            ],
            $this->getAttributeValue($this->builder, 'properties')
        );
    }

    /**
     *
     */
    public function test_embedded()
    {
        $this->assertInstanceOf(
            'Bdf\Api\Mapping\Builder\EmbeddedTypePropertyBuilder',
            $this->builder->embedded('property', 'ComplexType')
        );

        $this->assertEquals(
            [
                'property' => (new EmbeddedTypePropertyBuilder($this->registry))->name('property')->type('ComplexType')
            ],
            $this->getAttributeValue($this->builder, 'properties')
        );
    }

    /**
     * @dataProvider propertyAddingMethodsProvider
     *
     * @param string $method
     * @param string $type
     */
    public function test_propertyMethods($method, $type)
    {
        /** @var PropertyMetadata $metadata */
        $metadata = $this->builder->$method('property')->build(new ServiceBuilder());

        $this->assertSame('property', $metadata->getName());
        $this->assertSame($type, $metadata->getType()->getName());
    }

    /**
     * @return array
     */
    public function propertyAddingMethodsProvider()
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
        ];
    }

    /**
     *
     */
    public function test_array_property()
    {
        $this->builder->simpleArray('property1');
        $this->builder->string('property2')->collection();
        $this->builder->property('property3', 'string[]');

        $this->assertEquals(
            [
                'property1' => (new TypePropertyBuilder($this->registry))->name('property1')->type('array'),
                'property2' => (new TypePropertyBuilder($this->registry))->name('property2')->type('array'),
                'property3' => (new TypePropertyBuilder($this->registry))->name('property3')->type('string[]'),
            ],
            $this->getAttributeValue($this->builder, 'properties')
        );
    }

    /**
     *
     */
    public function test_build()
    {
        $this->builder->name('param');
        $this->builder->className('Toto');

        $this->assertEquals(
            new ComplexTypeMetadata('param', 'Toto'),
            $this->builder->build($this->serviceBuilder)
        );
    }

    /**
     *
     */
    public function test_build_with_properties()
    {
        $this->serviceBuilder->expects($this->at(0))->method('buildType')
            ->with('boolean')
            ->will($this->returnValue(new SimpleTypeMetadata('boolean')))
        ;

        $this->serviceBuilder->expects($this->at(1))->method('buildType')
            ->with('string')
            ->will($this->returnValue(new SimpleTypeMetadata('string')))
        ;

        $this->builder->boolean('param1');
        $this->builder->string('param2');

        $this->assertEquals(
            (new ComplexTypeMetadata(null, 'stdClass'))
                ->addProperty(new DefaultPropertyMetadata('param1', new SimpleTypeMetadata('boolean')))
                ->addProperty(new DefaultPropertyMetadata('param2', new SimpleTypeMetadata('string'))),
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
