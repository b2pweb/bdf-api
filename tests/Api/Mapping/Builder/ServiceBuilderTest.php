<?php

namespace Bdf\Api\Mapping\Builder;

use Bdf\Api\Mapping\Metadata\MethodMetadata;
use Bdf\Api\Mapping\Metadata\ParameterMetadata;
use Bdf\Api\Mapping\Metadata\ServiceMetadata;
use Bdf\Api\Mapping\Metadata\Types\ArrayTypeMetadata;
use Bdf\Api\Mapping\Metadata\Types\ComplexTypeMetadata;
use Bdf\Api\Mapping\Metadata\Types\DefaultPropertyMetadata;
use Bdf\Api\Mapping\Metadata\Types\OptionTypeMetadata;
use Bdf\Api\Mapping\Metadata\Types\SimpleTypeMetadata;
use Bdf\Api\Mapping\Registry;
use PHPUnit\Framework\TestCase;

/**
 * Class ServiceBuilderTest
 *
 * @package Bdf\Api\Mapping\Builder
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Builder
 * @group Bdf_Api_Mapping_Builder_ServiceBuilder
 */
class ServiceBuilderTest extends TestCase
{
    /**
     * @var ServiceBuilder
     */
    protected $builder;

    /**
     * @var Registry
     */
    protected $registry;


    public function setUp(): void
    {
        parent::setUp();

        $this->registry = $this->createMock('Bdf\Api\Mapping\Registry');
        $this->builder = new ServiceBuilder($this->registry);
    }

    /**
     *
     */
    public function test_method()
    {
        $this->builder->method('method1');
        $this->builder->method('method2');

        $this->assertEquals(
            [
                'method1' => (new MethodBuilder($this->registry))->name('method1'),
                'method2' => (new MethodBuilder($this->registry))->name('method2'),
            ],
            $this->getAttributeValue($this->builder, 'methods')
        );
    }

    /**
     *
     */
    public function test_method_with_initializer()
    {
        $this->builder->method('method1', function(MethodBuilder $builder) {
            $builder->boolean('param');
        });

        $expected = [
            'method1' => (new MethodBuilder($this->registry))->name('method1')
        ];

        $expected['method1']->boolean('param');

        $this->assertEquals(
            $expected,
            $this->getAttributeValue($this->builder, 'methods')
        );
    }

    /**
     *
     */
    public function test_type()
    {
        $this->builder->type('Type1', 'Namespace\Type1');
        $this->builder->type('Type2', 'Namespace\Type2');

        $this->assertEquals(
            [
                'Type1' => (new TypeBuilder($this->registry))->name('Type1')->className('Namespace\Type1'),
                'Type2' => (new TypeBuilder($this->registry))->name('Type2')->className('Namespace\Type2'),
            ],
            $this->getAttributeValue($this->builder, 'types')
        );
    }

    /**
     *
     */
    public function test_type_with_initializer()
    {
        $this->builder->type('Type1', 'Namespace\Type1', function(TypeBuilder $builder) {
            $builder->string('property');
        });

        $expected = [
            'Type1' => (new TypeBuilder($this->registry))->name('Type1')->className('Namespace\Type1')
        ];

        $expected['Type1']->string('property');

        $this->assertEquals(
            $expected,
            $this->getAttributeValue($this->builder, 'types')
        );
    }

    /**
     *
     */
    public function test_build()
    {
        $this->assertEquals(
            new ServiceMetadata(),
            $this->builder->build()
        );
    }

    /**
     *
     */
    public function test_build_with_types()
    {
        $this->builder->type('Type1', 'Namespace\Type1', function(TypeBuilder $builder) {
            $builder->string('name');
        });

        $this->assertEquals(
            (new ServiceMetadata())
                ->addType(new ComplexTypeMetadata('Type1', 'Namespace\Type1', [
                    new DefaultPropertyMetadata('name', new SimpleTypeMetadata('string'))
                ]))
                ->addType(new SimpleTypeMetadata('string')),
            $this->builder->build()
        );
    }

    /**
     *
     */
    public function test_build_with_methods()
    {
        $this->builder->method('method1', function(MethodBuilder $builder) {
            $builder->boolean('added');
        });

        $this->assertEquals(
            (new ServiceMetadata())
                ->addMethod(new MethodMetadata('method1', [
                    new ParameterMetadata('added', new SimpleTypeMetadata('boolean'))
                ]))
                ->addType(new SimpleTypeMetadata('boolean')),
            $this->builder->build()
        );
    }

    /**
     *
     */
    public function test_buildType_with_simpleType()
    {
        $this->assertEquals(
            new SimpleTypeMetadata('string'),
            $this->builder->buildType('string')
        );

        $this->assertEquals(
            ['string' => new SimpleTypeMetadata('string')],
            $this->getAttributeValue($this->builder, 'buildedTypes')
        );

        $this->assertEquals(
            (new ServiceMetadata())
                ->addType(new SimpleTypeMetadata('string')),
            $this->getAttributeValue($this->builder, 'metadata')
        );
    }

    /**
     *
     */
    public function test_buildType_with_arrayType()
    {
        $this->assertEquals(
            new ArrayTypeMetadata(new SimpleTypeMetadata('string')),
            $this->builder->buildType('string[]')
        );

        $this->assertEquals(
            [
                'string'   => new SimpleTypeMetadata('string'),
                'string[]' => new ArrayTypeMetadata(new SimpleTypeMetadata('string'))
            ],
            $this->getAttributeValue($this->builder, 'buildedTypes')
        );

        $this->assertEquals(
            (new ServiceMetadata())
                ->addType(new SimpleTypeMetadata('string'))
                ->addType(new ArrayTypeMetadata(new SimpleTypeMetadata('string'))),
            $this->getAttributeValue($this->builder, 'metadata')
        );
    }

    /**
     *
     */
    public function test_buildType_with_optionType()
    {
        $this->assertEquals(
            new OptionTypeMetadata(),
            $this->builder->buildType('options')
        );

        $this->assertEquals(
            [
                'options' => new OptionTypeMetadata()
            ],
            $this->getAttributeValue($this->builder, 'buildedTypes')
        );

        $this->assertEquals(
            (new ServiceMetadata())
                ->addType(new OptionTypeMetadata()),
            $this->getAttributeValue($this->builder, 'metadata')
        );
    }

    /**
     *
     */
    public function test_buildType_with_complexType()
    {
        $this->builder->type('Type1', 'Namespace\Type1', function(TypeBuilder $builder) {
            $builder->string('prop1');
            $builder->boolean('prop2');
        });

        $type1Metadata = new ComplexTypeMetadata('Type1', 'Namespace\Type1', [
            new DefaultPropertyMetadata('prop1', new SimpleTypeMetadata('string')),
            new DefaultPropertyMetadata('prop2', new SimpleTypeMetadata('boolean')),
        ]);

        $this->assertEquals(
            $type1Metadata,
            $this->builder->buildType('Type1')
        );

        $this->assertEquals(
            [
                'string' => new SimpleTypeMetadata('string'),
                'boolean' => new SimpleTypeMetadata('boolean'),
                'Type1' => $type1Metadata,
            ],
            $this->getAttributeValue($this->builder, 'buildedTypes')
        );

        $this->assertEquals(
            (new ServiceMetadata())
                ->addType(new SimpleTypeMetadata('string'))
                ->addType(new SimpleTypeMetadata('boolean'))
                ->addType($type1Metadata),
            $this->getAttributeValue($this->builder, 'metadata')
        );
    }

    /**
     *
     */
    public function test_buildType_with_unknownType()
    {
        $this->expectException('Bdf\Api\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The mapping type "Type1" is not defined');

        $this->builder->buildType('Type1');
    }

    private function getAttributeValue(object $object, string $property): mixed
    {
        $fn = fn () => $object->$property;
        $fn = $fn->bindTo(null, $object);

        return $fn();
    }
}
