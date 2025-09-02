<?php

namespace Bdf\Api\Protocol;

use Bdf\Api\ApiRequest;
use Bdf\Api\Definition\Renderer\RendererInterface;
use Bdf\Api\Server\Response\ResponseInterface;
use Bdf\Api\Server\ServerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * AbstractProtocol
 */
abstract class AbstractProtocol implements ProtocolInterface
{
    protected ContainerInterface $di;
    protected array $options;

    public function __construct(ContainerInterface $di, array $options = [])
    {
        $this->di = $di;
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ApiRequest $request): Response
    {
        $server = $this->createServer($request);

        $this->configureServer($server, $request);

        if ($this->isServiceMapRequest($request)) {
            return $this->handleServiceMapRequest($server, $request);
        } else {
            return $this->handleServiceRequest($server, $request);
        }
    }

    /**
     * @param ServerInterface $server
     * @param ApiRequest $request
     *
     * @throws \Exception
     */
    private function configureServer(ServerInterface $server, ApiRequest $request): void
    {
        $server->setDefinition($request->api()->getDefinition());

        if ($request->api()->getServerListeners()) {
            $server->addListeners($request->api()->getServerListeners());
        }

        $serviceClass = $request->api()->getServiceClass();

        if (method_exists($serviceClass, 'getClassmap')) {
            $server->setClassmap($serviceClass::getClassmap());
        }

        $server->setClass(new $serviceClass($this->di, $request));
    }

    /**
     * @param ServerInterface $server
     * @param ApiRequest $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    private function handleServiceMapRequest(ServerInterface $server, ApiRequest $request): Response
    {
        if ($request->hasException()) {
            throw $request->getException();
        }

        return new Response(
            $this->createRenderer($request)->render($server->getDefinition(), $request->getApiUri()),
            Response::HTTP_OK,
            $this->getHttpResponseHeaders()
        );
    }

    /**
     * @param ServerInterface $server
     * @param ApiRequest $request
     *
     * @return Response
     */
    private function handleServiceRequest(ServerInterface $server, ApiRequest $request): Response
    {
        if ($request->hasException()) {
            $server->fault($request->getException());
        }

        $server->setAutoEmitResponse(false);

        return $this->createHttpResponse($server->handle());
    }

    /**
     * Get http response headers
     *
     * @return array
     */
    protected function getHttpResponseHeaders(): array
    {
        return array_merge(
            $this->getAdditionnalHeaders(),
            $this->options['headers'] ?? []
        );
    }
    
    /**
     * Get additionnal http headers.
     *
     * This method should be overwritten to add protocol headers
     *
     * @return array
     */
    protected function getAdditionnalHeaders()
    {
        return [];
    }

    /**
     * Create server from request
     *
     * @param ApiRequest $request
     *
     * @return ServerInterface
     */
    abstract protected function createServer(ApiRequest $request);

    /**
     * Create service map renderer
     *
     * @param ApiRequest $request
     *
     * @return RendererInterface
     */
    abstract protected function createRenderer(ApiRequest $request);

    /**
     * Create http response from server response
     *
     * @param ResponseInterface $serverResponse
     *
     * @return Response
     */
    abstract protected function createHttpResponse(ResponseInterface $serverResponse);
}
