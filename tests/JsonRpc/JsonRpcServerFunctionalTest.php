<?php

namespace Bdf\JsonRpc;

use Bdf\Fixtures\TestApplication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

use function usleep;

class JsonRpcServerFunctionalTest extends TestCase
{
    private TestApplication $app;
    private array $proc = [];

    protected function setUp(): void
    {
        $this->app = new TestApplication();
        $this->app->boot();
    }

    protected function tearDown(): void
    {
        foreach ($this->proc as $pid) {
            @posix_kill($pid, SIGKILL);
            usleep(50000);
        }

        $this->proc = [];
    }

    public function test_definition()
    {
        $response = $this->app->handle(Request::create('/api/jsonrpc/my_secret_key/myservice', 'GET'));
        $expected = [
            'transport' => 'POST',
            'envelope' => 'JSON-RPC-2.0',
            'contentType' => 'application/json',
            'SMDVersion' => '2.0',
            'target' => 'http://localhost/api/jsonrpc/my_secret_key/myservice',
            'services' => [
                'hello' => [
                    'envelope' => 'JSON-RPC-2.0',
                    'transport' => 'POST',
                    'parameters' => [
                        [
                            'type' => 'string',
                            'name' => 'name',
                            'optional' => false,
                            'default' => 'John',
                            'description' => null,
                        ],
                    ],
                    'returns' => 'object',
                ],
            ],
        ];

        $this->assertEquals($expected, json_decode($response->getContent(), true));
    }

    public function test_call()
    {
        $this->startServer();

        $client = new JsonRpcClient('http://127.0.0.1:5000/api/jsonrpc/my_secret_key/myservice');
        $result = $client->call('hello', ['name' => 'World']);

        $this->assertSame([
            'result' => 'Hello World',
            'success' => true,
        ], $result);
    }

    public function test_call_invalid_method()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Method not found');

        $this->startServer();

        $client = new JsonRpcClient('http://127.0.0.1:5000/api/jsonrpc/my_secret_key/myservice');
        $client->call('invalid');
    }

    public function test_call_error()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('invalid name');

        $this->startServer();

        $client = new JsonRpcClient('http://127.0.0.1:5000/api/jsonrpc/my_secret_key/myservice');
        $client->call('hello', ['name' => 'error']);
    }

    private function startServer(): void
    {
        $cmd = 'php -S 127.0.0.1:5000 ' . escapeshellarg(__DIR__ . '/../Fixtures/api.php') . ' >> '.escapeshellarg(__DIR__ . '/../../api.log').' 2>&1 & echo $!';
        $pid = (int) shell_exec($cmd);

        // Wait for server to be up
        $tries = 0;
        while ($tries++ < 50) {
            $fp = @fsockopen('127.0.0.1', 5000, $errno, $errstr, 0.1);
            if ($fp) {
                fclose($fp);
                break;
            }
            usleep(100000);
        }

        $this->proc[] = $pid;
        usleep(50000);
    }
}
