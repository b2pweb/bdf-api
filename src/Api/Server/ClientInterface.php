<?php

namespace Bdf\Api\Server;

/**
 * Interface ClientInterface
 *
 * @package Bdf\Api\Server
 */
interface ClientInterface
{
    /**
     * Executes remote call
     *
     * Unified interface for calling custom remote methods.
     *
     * @param  string $method Remote call name.
     * @param  mixed  $params Call parameters.
     * 
     * @return mixed Remote call results.
     */
    public function call($method, $params = array());
    
    /**
     * @return string
     */
    public function getLastRequest();

    /**
     * @return string
     */
    public function getLastResponse();
    
    /**
     * Retrieve request headers
     *
     * @return string
     */
    public function getLastRequestHeaders();

    /**
     * Retrieve response headers (as string)
     *
     * @return string
     */
    public function getLastResponseHeaders();
}