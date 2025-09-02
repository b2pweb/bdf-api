<?php

namespace Bdf\Soap\Server;

use Bdf\Api\Server\Response\ResponseInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Soap
 * @group Bdf_Soap_Server
 */
class ResponseTest extends TestCase
{
    /**
     *
     */
    public function test_runtime()
    {
        $response = new Response();

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
