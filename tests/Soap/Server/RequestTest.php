<?php

namespace Bdf\Soap\Server;

use PHPUnit\Framework\TestCase;
use SoapFault;

/**
 * @group Bdf_Soap
 * @group Bdf_Soap_Server
 */
class RequestTest extends TestCase
{
    /**
     *
     */
    public function test_validate_empty_body()
    {
        $this->expectException(SoapFault::class);
        $this->expectExceptionMessage('Invalid XML');

        $request = new Request();
        $request->validate();
    }

    /**
     *
     */
    public function test_validate_invalid_xml()
    {
        $this->expectException(SoapFault::class);
        $this->expectExceptionMessage('Invalid XML');

        $request = new Request('Foo');
        $request->validate();
    }

    /**
     *
     */
    public function test_validate()
    {
        $xml = <<<EOF
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
    <s:Body xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
        <get xmlns="http://acme.com">
            <id xmlns="">john</id>
        </get>
    </s:Body>
</s:Envelope>
EOF;

        $request = new Request($xml);
        $request->prepare();

        $this->assertNull($request->validate());
    }
}
