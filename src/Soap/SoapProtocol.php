<?php

namespace Bdf\Soap;

use Bdf\Api\ApiRequest;
use Bdf\Api\Protocol\AbstractProtocol;
use Bdf\Api\Server\Response\ResponseInterface;
use Bdf\Soap\Renderer\WsdlRenderer;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 * SoapProtocol
 */
class SoapProtocol extends AbstractProtocol
{
    /**
     * Generates the WSDL internally (without using a file or external URL)
     */
    public const INTERNAL_WSDL = 'internal_wsdl';

    /**
     * {@inheritdoc}
     */
    public function isServiceMapRequest(ApiRequest $request): bool
    {
        return $request->query->has('wsdl');
    }

    /**
     * {@inheritdoc}
     */
    protected function createServer(ApiRequest $request): SoapServer
    {
        $server = new SoapServer();

        $server->setOptions([
            'cache_wsdl' => ini_get('soap.wsdl_cache_enabled') ? ini_get('soap.wsdl_cache') : \WSDL_CACHE_NONE,
            'document'   => $request->attributes->get('style', 'rpc-encoded'),
            'wsdl'       => empty($this->options[self::INTERNAL_WSDL])
                ? $request->getApiUri() . '?wsdl'
                : fn (SoapServer $server) => 'data://text/xml;base64,' . base64_encode($this->createRenderer($request)->render($server->getDefinition(), $request->getApiUri()))
            ,
        ]);

        return $server;
    }

    /**
     * {@inheritdoc}
     */
    protected function createRenderer(ApiRequest $request): WsdlRenderer
    {
        $renderer = new WsdlRenderer();

        if ($request->attributes->get('style')) {
            $renderer->setDocumentStyle($request->attributes->get('style'));
        }

        $renderer->setFlags($request->getApiOptions());

        return $renderer;
    }

    /**
     * {@inheritdoc}
     */
    protected function createHttpResponse(ResponseInterface $serverResponse): BaseResponse
    {
        return new BaseResponse((string)$serverResponse, BaseResponse::HTTP_OK, $this->getHttpResponseHeaders());
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdditionnalHeaders(): array
    {
        return [
            'Content-Type' => 'text/xml'
        ];
    }

    /**
     * @return array
     */
    /*protected function getTypemap()
    {
        return array(
            array(
                'type_ns'   => 'http://www.w3.org/2001/XMLSchema',
                'type_name' => 'dateTime',
                'from_xml'  => function ($xml) {
                    return (new \DateTime(strip_tags($xml)))->setTimezone(
                        new \DateTimeZone(date_default_timezone_get())
                    );
                },
                'to_xml'    => function ($value) {
                    return '<dateTime>' . $value->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format(\DateTime::W3C) . '</dateTime>';
                }
            )
        );
    }*/
}
