<?php

namespace Bdf\Api\Definition\Loader;

use Bdf\Api\Definition\ComplexTypeDefinition;
use Bdf\Api\Definition\MethodDefinition;
use Bdf\Api\Definition\ParameterDefinition;
use Bdf\Api\Definition\PropertyDefinition;
use Bdf\Api\Definition\ServiceDefinition;
use Bdf\Api\Definition\SimpleTypeDefinition;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

/**
 * Class PhpFileLoaderTest
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Definition
 * @group Bdf_Api_Definition_Loader
 * @group Bdf_Api_Definition_Loader_PhpFileLoader
 *
 * @package Bdf\Api\Definition\Loader
 *
 * @coversDefaultClass Bdf\Api\Definition\Loader\PhpFileLoader
 */
class PhpFileLoaderTest extends TestCase
{
    /**
     * 
     */
    public function test_load()
    {
        $loader = new PhpFileLoader(__DIR__ . '/Fixtures/definition.php');

        $this->assertEquals(
            (new ServiceDefinition('Hello'))
                ->setMethods([
                    (new MethodDefinition('test'))
                        ->setParameters([
                            new ParameterDefinition('offer', new ComplexTypeDefinition('Offer', [
                                (new PropertyDefinition('name', new SimpleTypeDefinition('string')))->setNillable(true),
                                (new PropertyDefinition('city', new SimpleTypeDefinition('string')))->setNillable(true),
                                (new PropertyDefinition('country', new SimpleTypeDefinition('string')))->setNillable(true),
                            ]))
                        ])
                        ->setReturn(new SimpleTypeDefinition('string'))
                ])
                ->setTypes([
                    'Offer' => new ComplexTypeDefinition('Offer', [
                        (new PropertyDefinition('name', new SimpleTypeDefinition('string')))->setNillable(true),
                        (new PropertyDefinition('city', new SimpleTypeDefinition('string')))->setNillable(true),
                        (new PropertyDefinition('country', new SimpleTypeDefinition('string')))->setNillable(true),
                    ])
                ]),
            $loader->load()
        );
    }
}
