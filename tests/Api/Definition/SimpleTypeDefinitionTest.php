<?php

namespace Bdf\Api\Definition;

use PHPUnit\Framework\TestCase;

/**
 * Class SimpleTypeDefinitionTest
 *
 * @package Bdf\Api\Definition
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Definition
 * @group Bdf_Api_Definition_SimpleTypeDefinition
 *
 * @coversDefaultClass Bdf\Api\Definition\SimpleTypeDefinition
 */
class SimpleTypeDefinitionTest extends TestCase
{
    public function test_should_have_a_name()
    {
        $definition = new SimpleTypeDefinition('int');

        $this->assertSame('int', $definition->getName());
    }
}
