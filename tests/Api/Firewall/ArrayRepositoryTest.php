<?php

namespace Bdf\Api\Firewall;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Firewall
 */
class ArrayRepositoryTest extends TestCase
{
    /**
     * 
     */
    public function test_basic_validation()
    {
        $firewall = new ArrayRepository([
            '192.168.0.*'
        ]);
        
        $this->assertTrue($firewall->isAllowed('192.168.0.1'));
        $this->assertFalse($firewall->isAllowed('192.168.1.1'));
    }
}
