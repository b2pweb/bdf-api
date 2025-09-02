<?php

namespace Bdf\Api\Mapping\Builder;

use Bdf\Api\Mapping\Metadata\ReturnMetadata;
use Bdf\Api\Mapping\Metadata\Types\SimpleTypeMetadata;
use Bdf\Api\Mapping\Registry;
use Bdf\Api\Mapping\Transformers\TransformerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ReturnBuilderTest
 *
 * @package Bdf\Api\Mapping\Builder
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Builder
 * @group Bdf_Api_Mapping_Builder_ReturnBuilder
 */
class ReturnBuilderTest extends TestCase
{
    /**
     * @var ReturnBuilder|MockObject
     */
    protected $builder;

    /**
     * @var ServiceBuilder
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
        $this->builder = new ReturnBuilder($this->registry);
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
            ->type('string')
            ->transformer('Test')
            ->build(new ServiceBuilder())
        ;

        $this->assertEquals('string', $metadata->getType()->getName());
        $this->assertEquals($transformer, $metadata->getTransformer());
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

        $this->builder->type('string');

        $this->assertEquals(
            new ReturnMetadata(new SimpleTypeMetadata('string')),
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

        $this->registry->expects($this->once())->method('getTransformer')
            ->with('transformer')
            ->will($this->returnValue($transformer))
        ;

        $this->builder->type('string');
        $this->builder->transformer('transformer');

        $this->assertEquals(
            (new ReturnMetadata(new SimpleTypeMetadata('string')))
                ->setTransformer($transformer),
            $this->builder->build($this->serviceBuilder)
        );
    }
}
