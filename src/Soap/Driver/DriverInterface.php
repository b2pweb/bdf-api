<?php

namespace Bdf\Soap\Driver;

/**
 * @package Bdf\Soap\Driver
 */
interface DriverInterface
{
    /**
     * @param string $function
     * @param array $arguments
     * @param array|null $inputHeaders
     * @param array|null $outputHeaders
     *
     * @return mixed
     */
    public function __soapCall($function, array $arguments = [], $inputHeaders = null, &$outputHeaders = null);

    /**
     * @return string|null
     */
    public function __getLastRequest();

    /**
     * @return string|null
     */
    public function __getLastResponse();

    /**
     * @return string|null
     */
    public function __getLastRequestHeaders();

    /**
     * @return string|null
     */
    public function __getLastResponseHeaders();

    /**
     * @return string[]|null
     */
    public function __getFunctions();

    /**
     * @return string[]|null
     */
    public function __getTypes();

    /**
     * @param string $name The method name
     * @param array $arguments The method arguments
     * @param string $documentType The SOAP document type (ex: 'document-literal')
     *
     * @return array The processed arguments
     */
    public function preProcessArguments($name, $arguments, $documentType);

    /**
     * @param string $name The method name
     * @param mixed $result The result from the SOAP call
     * @param string $documentType The SOAP document type (ex: 'document-literal')
     *
     * @return mixed The processed result
     */
    public function preProcessResult($name, $result, $documentType);

    /**
     * Initialize SOAP environment
     * 
     * @param string $wsdl
     * @param array $options
     *
     * @return void
     */
    public function initialize($wsdl, array $options = []);
}
