<?php

namespace Bdf\Soap\Exception;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Soap
 * @group Bdf_Soap_Exception
 */
class SoapExceptionTest extends TestCase
{
    /**
     *
     */
    public function test_runtime()
    {
        $exception = new RuntimeException();

        $this->assertInstanceOf(SoapException::class, $exception);
    }

    /**
     *
     */
    public function test_invalid_argument()
    {
        $exception = new InvalidArgumentException();

        $this->assertInstanceOf(SoapException::class, $exception);
    }
}
