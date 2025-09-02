<?php

namespace Bdf\Api;

use Bdf\Api\Definition\MethodDefinition;
use Bdf\Api\Definition\Renderer\RendererInterface;
use Bdf\Api\Definition\ServiceDefinition;
use Bdf\Api\Events\KernelEvents;
use Bdf\Api\Protocol\AbstractProtocol;
use Bdf\Api\Protocol\ProtocolFactory;
use Bdf\Api\Providers\ApiProviderInterface;
use Bdf\Api\Server\ApiServer;
use Bdf\Api\Server\Request\CustomRequest;
use Bdf\Api\Server\Response\ResponseInterface;
use Bdf\Fixtures\TestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * Class ApplicationTest
 */
class ApplicationTest extends TestCase
{
    private function getApplication(): Application
    {
        $app = new Application(new TestKernel('dev', true));
        $app->boot();

        $app->attach(
            new class implements ApiProviderInterface {
                public function configureApi(Api $api, Application $app)
                {
                    $api
                        ->setServiceClass(MyService::class)
                        ->setName('service')
                        ->setDefinition(
                            (new ServiceDefinition('foo'))
                                ->addMethod((new MethodDefinition('foo')))
                                ->addMethod((new MethodDefinition('bar')))
                        )
                    ;
                }
            },
            '/test/{protocol}'
        );
        $app->get(ProtocolFactory::class)->register('jsonrpc', MyJsonRpcProtocol::class);

        return $app;
    }

    /**
     *
     */
    public function test_server_exception_should_be_listen()
    {
        $app = $this->getApplication();
        $app->eventDispatcher()->addListener(KernelEvents::EXCEPTION, function(ExceptionEvent $event) use(&$exception) {
            $exception = $event->getThrowable();

            $event->setThrowable(new \Exception('My other message'));
        });

        $response = $app->handle(ApiRequest::create('http://127.0.0.1/test/jsonrpc', 'POST', [], [], [], [], json_encode(['jsonrpc' => '2.0', 'method' => 'foo', 'id' => 3])));
        $content = json_decode($response->getContent(), true);

        $this->assertEquals(new \Exception('my error'), $exception);
        $this->assertEquals('My other message', $content);
    }

    /**
     *
     */
    public function test_success()
    {
        $app = $this->getApplication();
        $app->eventDispatcher()->addListener(KernelEvents::EXCEPTION, function(ExceptionEvent $event) use(&$exception) {
            $exception = $event->getThrowable();
            echo $exception;

            $event->setThrowable(new \Exception('My other message'));
        });

        $response = $app->handle(ApiRequest::create('http://127.0.0.1/test/jsonrpc', 'POST', [], [], [], [], json_encode(['jsonrpc' => '2.0', 'method' => 'bar', 'id' => 3])));
        $content = json_decode($response->getContent(), true);

        $this->assertSame(['hello' => 'world'], $content);
    }
}

class MyService
{
    public function foo()
    {
        throw new \Exception('my error');
    }

    public function bar()
    {
        return ['hello' => 'world'];
    }
}

class MyJsonRpcProtocol extends AbstractProtocol
{
    protected function createServer(ApiRequest $request): ApiServer
    {
        $server = new ApiServer();

        $body = json_decode($request->getContent(), true);
        $server->setRequest(new CustomRequest(
            $body['method'] ?? null,
            $body,
        ));

        return $server;
    }

    protected function createRenderer(ApiRequest $request): RendererInterface
    {
        return new class implements RendererInterface {
            public function render(ServiceDefinition $definition, $uri)
            {
                return json_encode($definition);
            }
        };
    }

    protected function createHttpResponse(ResponseInterface $serverResponse): JsonResponse
    {
        return new JsonResponse($serverResponse->getResult());
    }

    public function isServiceMapRequest(ApiRequest $request)
    {
        return false;
    }
}
