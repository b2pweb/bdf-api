<?php

namespace Bdf\Api\Mapping\Metadata;

use Bdf\Api\Mapping\Context;
use Bdf\Api\Mapping\Metadata\Types\TypeMetadataInterface;
use DI\Container;
use PHPUnit\Framework\TestCase;

/**
 * Class ReturnMetadataTest
 *
 * @package Bdf\Api\Mapping\Metadata
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Metadata
 * @group Bdf_Api_Mapping_Metadata_ReturnMetadata
 *
 * @coversDefaultClass Bdf\Api\Mapping\Metadata\ReturnMetadata
 */
class ReturnMetadataTest extends TestCase
{
    /**
     * @var ReturnMetadata
     */
    protected $metadata;

    /**
     * @var TypeMetadataInterface
     */
    protected $type;


    public function setUp(): void
    {
        $this->type = $this->createMock('Bdf\Api\Mapping\Metadata\Types\TypeMetadataInterface');
        $this->metadata = new ReturnMetadata($this->type);
    }

    /**
     *
     */
    public function test_construct()
    {
        $this->assertEquals($this->type, $this->metadata->getType());
    }

    /**
     *
     */
    public function test_unmap_with_null()
    {
        $this->type->expects($this->never())->method('unmap');

        $this->assertNull($this->metadata->unmap(null, new Context(new Container())));
    }

    /**
     *
     */
    public function test_unmap()
    {
        $context = new Context(new Container());

        $this->type->expects($this->once())->method('unmap')
            ->with('bonjour', $context)
            ->will($this->returnArgument(0))
        ;

        $this->assertEquals('bonjour', $this->metadata->unmap('bonjour', $context));
    }

    /**
     *
     */
    public function test_unmap_with_transformer()
    {
        $context = new Context(new Container());

        $transformer = $this->createMock('Bdf\Api\Mapping\Transformers\TransformerInterface');

        $transformer->expects($this->once())->method('undoTransform')
            ->with('test', $context)
            ->will($this->returnValue('testtest'))
        ;

        $this->metadata->setTransformer($transformer);

        $this->type->expects($this->once())->method('unmap')
            ->with('testtest', $context)
            ->will($this->returnArgument(0))
        ;

        $this->assertEquals('testtest', $this->metadata->unmap('test', $context));
    }
}
