<?php

namespace Bdf\Api\Mapping\Metadata\Types;

use Bdf\Api\Mapping\Context;
use DI\Container;
use PHPUnit\Framework\TestCase;

/**
 * Class SimpleTypeMetadataTest
 *
 * @package Bdf\Api\Mapping\Metadata\Types
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Metadata
 * @group Bdf_Api_Mapping_Metadata_Types
 * @group Bdf_Api_Mapping_Metadata_Types_SimpleTypeMetadata
 *
 * @coversDefaultClass Bdf\Api\Mapping\Metadata\Types\SimpleTypeMetadata
 */
class SimpleTypeMetadataTest extends TestCase
{
    /**
     * @dataProvider mapProvider
     *
     * @param string $type
     * @param mixed $source
     */
    public function test_map($type, $source)
    {
        $metadata = new SimpleTypeMetadata($type);

        $this->assertEquals($type, $metadata->getName());
        $this->assertEquals($source, $metadata->map($source, new Context(new Container())));
    }

    /**
     * @return array
     */
    public function mapProvider()
    {
        return [
            [
                'string',
                'test_map'
            ],
            [
                'int',
                33
            ],
            [
                'double',
                5.6
            ],
        ];
    }

    /**
     * @dataProvider unmapProvider
     *
     * @param string $type
     * @param mixed $source
     */
    public function test_unmap($type, $source)
    {
        $metadata = new SimpleTypeMetadata($type);

        $this->assertEquals($type, $metadata->getName());
        $this->assertEquals($source, $metadata->unmap($source, new Context(new Container())));
    }

    /**
     * @return array
     */
    public function unmapProvider()
    {
        return [
            [
                'string',
                'test_unmap'
            ],
            [
                'int',
                66
            ],
            [
                'double',
                6.2
            ],
        ];
    }
}
