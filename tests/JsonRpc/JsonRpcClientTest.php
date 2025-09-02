<?php

namespace Bdf\JsonRpc;

/**
 * @group Bdf_JsonRpc
 * @group Bdf_JsonRpc_Client
 * @group Bdf_JsonRpc_JsonRpcClient
 */
class JsonRpcClientTest extends \PHPUnit\Framework\TestCase
{
    private static $pid;

    public static function setUpBeforeClass(): void
    {
        $command =  PHP_BINARY . ' -S 127.0.0.1:5000 ' . escapeshellarg(__DIR__.'/_files/server.php') . ' > /dev/null 2>&1 & echo $!;';
        $pid = exec($command, $output);

        self::$pid = (int) $pid;
        sleep(1);
    }

    public static function tearDownAfterClass(): void
    {
        posix_kill(self::$pid, SIGTERM);
    }

    /**
     * 
     */
    public function test_default_values()
    {
        $client = new JsonRpcClient();
        
        $this->assertEquals('', $client->getUri());
        $this->assertEquals(1, $client->getId());
        $this->assertEquals([], $client->getOptions());
        $this->assertEquals(JsonRpcClient::SINGLE_ELEMENT_ARRAY, $client->getFeatures());
        $this->assertEquals(JsonRpcClient::VERSION_2, $client->getVersion());
        $this->assertEquals(true, $client->getAutoValidate());
    }
    
    /**
     * 
     */
    public function test_construct_set_options()
    {
        $client = new JsonRpcClient('uri', ['timeout' => 10], 2);
        
        $this->assertEquals('uri', $client->getUri());
        $this->assertEquals(2, $client->getId());
        $this->assertEquals(10, $client->getOption('timeout'));
    }
    
    /**
     * 
     */
    public function test_set_get_uri()
    {
        $client = new JsonRpcClient();
        
        $client->setUri('uri');
        
        $this->assertEquals('uri', $client->getUri());
    }
    
    /**
     * 
     */
    public function test_set_get_features()
    {
        $client = new JsonRpcClient();
        
        $client->setFeatures(JsonRpcClient::SINGLE_ELEMENT_OBJECT);
        
        $this->assertEquals(JsonRpcClient::SINGLE_ELEMENT_OBJECT, $client->getFeatures());
    }
    
    /**
     * 
     */
    public function test_set_get_autoValidate()
    {
        $client = new JsonRpcClient();
        
        $client->setAutoValidate(false);
        
        $this->assertEquals(false, $client->getAutoValidate());
    }
    
    /**
     * 
     */
    public function test_set_get_version()
    {
        $client = new JsonRpcClient();
        
        $client->setVersion(JsonRpcClient::VERSION_1);
        
        $this->assertEquals(null, $client->getVersion());
    }
    
    /**
     * Assert for prepareRequest tests
     */
    protected function assertPrepareRequest($expected, $values)
    {
        $expected['method'] = $values['method'] = 'method';
        $expected['params'] = $values['params'] = ['param1', 'param2'];
        
        $client = new JsonRpcClient('', [
            'version' => $values['version']
        ], $values['id']);
        
        $result = $this->callMethod($client, 'prepareRequest', $values['method'], $values['params'], $values['notification']);
        
        $this->assertEquals($expected, $result);
    }
    
    /**
     * 
     */
    public function test_prepareRequest_version2()
    {
        $expected = [
            'jsonrpc' => JsonRpcClient::VERSION_2,
            'id'      => 1,
        ];
        
        $this->assertPrepareRequest($expected, [
            'version'       => JsonRpcClient::VERSION_2,
            'id'            => 0,
            'notification'  => false,
        ]);
    }
    
    /**
     * 
     */
    public function test_prepareRequest_version1()
    {
        $expected = [
            'id' => 1
        ];
        
        $this->assertPrepareRequest($expected, [
            'version'       => JsonRpcClient::VERSION_1,
            'id'            => 0,
            'notification'  => false,
        ]);
    }
    
    /**
     * 
     */
    public function test_prepareRequest_notifiy_set_id_to_null_on_v1()
    {
        $expected = [
            'id' => null
        ];
        
        $this->assertPrepareRequest($expected, [
            'version'       => JsonRpcClient::VERSION_1,
            'id'            => 1,
            'notification'  => true,
        ]);
    }
    
    /**
     * 
     */
    public function test_prepareRequest_notifiy_unset_on_v2()
    {
        $expected = [
            'jsonrpc' => JsonRpcClient::VERSION_2,
        ];
        
        $this->assertPrepareRequest($expected, [
            'version'       => JsonRpcClient::VERSION_2,
            'id'            => 1,
            'notification'  => true,
        ]);
    }
    
    /**
     * 
     */
    public function test_parseResponse_returns_result()
    {
        $client = new JsonRpcClient();
        
        $this->assertEquals('test', $this->callMethod($client, 'parseResponse', [
            'id'     => 1,
            'result' => 'test',
        ]));
    }
    
    /**
     * 
     */
    public function test_parseResponse_should_have_id()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('No response ID in response');

        $client = new JsonRpcClient();
        
        $this->assertEquals('test', $this->callMethod($client, 'parseResponse', [
            'result' => 'test',
        ]));
    }
    
    /**
     * 
     */
    public function test_parseResponse_should_same_id()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid response ID #1 != #2');

        $client = new JsonRpcClient();
        
        $this->assertEquals('test', $this->callMethod($client, 'parseResponse', [
            'id' => 2,
            'result' => 'test',
        ]));
    }
    
    /**
     * 
     */
    public function test_parseResponse_have_result()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid response: ');

        $client = new JsonRpcClient();
        
        $this->assertEquals('test', $this->callMethod($client, 'parseResponse', ['id' => 1]));
    }
    
    /**
     * 
     */
    public function test_parseResponse_manage_error()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('expected error', 12);

        $client = new JsonRpcClient();
        
        $this->assertEquals('test', $this->callMethod($client, 'parseResponse', [
            'error' => [
                'code' => 12,
                'message' => 'expected error'
            ]
        ]));
    }

    public function test_functional_success()
    {
        $client = new JsonRpcClient('http://127.0.0.1:5000');

        $this->assertSame(['hello', 'world'], $client->echo('hello', 'world'));

        $this->assertSame('echo', $client->getLastMethod());
        $this->assertSame('{"jsonrpc":"2.0","method":"echo","params":["hello","world"],"id":2}', $client->getLastRequest());
        $this->assertStringContainsString('Content-Type: application/json', $client->getLastRequestHeaders());
        $this->assertSame('{"jsonrpc":"2.0","result":["hello","world"],"id":2}', $client->getLastResponse());
        $this->assertStringContainsString('Content-Type: application/json-rpc', $client->getLastResponseHeaders());
    }

    public function test_functional_error()
    {
        $client = new JsonRpcClient('http://127.0.0.1:5000');

        try {
            $client->invalid('test');
            $this->fail('Should throw an exception');
        } catch (\Exception $e) {
            $this->assertSame('Method not found', $e->getMessage());
        }

        $this->assertSame('invalid', $client->getLastMethod());
        $this->assertSame('{"jsonrpc":"2.0","method":"invalid","params":["test"],"id":2}', $client->getLastRequest());
        $this->assertStringContainsString('Content-Type: application/json', $client->getLastRequestHeaders());
        $this->assertSame('{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":2}', $client->getLastResponse());
        $this->assertStringContainsString('Content-Type: application/json-rpc', $client->getLastResponseHeaders());
    }

    private function callMethod(object $object, string $method, mixed ...$args): mixed
    {
        $fn = fn () => $object->$method(...$args);
        $fn = $fn->bindTo(null, $object);

        return $fn();
    }
}
