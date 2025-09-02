<?php

namespace Bdf\Api\Mapping\Loader;

use Bdf\Api\Mapping\Metadata\MethodMetadata;
use Bdf\Api\Mapping\Metadata\ParameterMetadata;
use Bdf\Api\Mapping\Metadata\ReturnMetadata;
use Bdf\Api\Mapping\Metadata\ServiceMetadata;
use Bdf\Api\Mapping\Metadata\Types\ComplexTypeMetadata;
use Bdf\Api\Mapping\Metadata\Types\DefaultPropertyMetadata;
use Bdf\Api\Mapping\Metadata\Types\SimpleTypeMetadata;
use PHPUnit\Framework\TestCase;

/**
 * Class PhpFileLoaderTest
 *
 * @package Bdf\Api\Mapping\Loader
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Loader
 * @group Bdf_Api_Mapping_Loader_PhpFileLoader
 *
 * @coversDefaultClass Bdf\Api\Mapping\Loader\PhpFileLoader
 */
class PhpFileLoaderTest extends TestCase
{
    /**
     *
     */
    public function test_load()
    {
        $this->assertEquals(
            (new ServiceMetadata())
                ->addMethod(
                    (new MethodMetadata('method1', [
                        new ParameterMetadata('param1', new SimpleTypeMetadata('string')),
                        new ParameterMetadata('param2', new SimpleTypeMetadata('integer'))
                    ]))
                    ->setReturn(new ReturnMetadata(new SimpleTypeMetadata('double')))
                )
                ->addType(new ComplexTypeMetadata('Offer', 'Bdf\Api\Mapping\Loader\Fixtures\Offer', [
                    new DefaultPropertyMetadata('name', new SimpleTypeMetadata('string')),
                    new DefaultPropertyMetadata('date', new SimpleTypeMetadata('string'))
                ]))
                ->addType(new SimpleTypeMetadata('string'))
                ->addType(new SimpleTypeMetadata('integer'))
                ->addType(new SimpleTypeMetadata('double')),
            (new PhpFileLoader(__DIR__ . '/Fixtures/mapping.php'))->load()
        );
    }
}
