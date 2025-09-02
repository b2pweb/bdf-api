<?php

namespace Bdf\JsonRpc;

use Bdf\Api\ApiRequest;
use Bdf\Api\Protocol\AbstractProtocol;
use Bdf\Api\Server\Response\ResponseInterface;
use Bdf\JsonRpc\Renderer\JsonRpcRenderer;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 * JsonRpcProtocol
 */
class JsonRpcProtocol extends AbstractProtocol
{
    /**
     * {@inheritdoc}
     */
    public function isServiceMapRequest(ApiRequest $request)
    {
        return $request->isMethod('GET');
    }

    /**
     * {@inheritdoc}
     */
    protected function createServer(ApiRequest $request)
    {
        return new JsonRpcServer();
    }

    /**
     * {@inheritdoc}
     */
    protected function createRenderer(ApiRequest $request)
    {
        return new JsonRpcRenderer();
    }

    /**
     * {@inheritdoc}
     */
    protected function createHttpResponse(ResponseInterface $serverResponse)
    {
        if (!$serverResponse->isError() && (null === $serverResponse->getId())) {
            return new BaseResponse('', BaseResponse::HTTP_NO_CONTENT, $this->getXdomainHeaders());
        }
        
        return new BaseResponse((string)$serverResponse, BaseResponse::HTTP_OK, $this->getHttpResponseHeaders());
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdditionnalHeaders()
    {
        return [
            'Content-Type' => 'application/json',
        ] + $this->getXdomainHeaders();
    }

    /**
     * X-domain headers
     * 
     * @return array
     */
    protected function getXdomainHeaders()
    {
        return [
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Headers' => 'Origin,Content-Type,Accept,Authorization',
        ];
    }
}
