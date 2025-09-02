<?php

namespace Bdf\Api\Server\Response;

use Bdf\Api\Server\Request\RequestInterface;

/**
 * 
 */
interface ResponseInterface
{
    /**
     * Prepare response for display. Request allow response format
     * 
     * @param RequestInterface $request
     */
    public function prepare(RequestInterface $request);
    
    /**
     * Set the server result
     * 
     * @param mixed $result
     */
    public function setResult($result);
    
    /**
     * Get the server result
     *
     * @return mixed
     */
    public function getResult();
    
    /**
     * Send response to output display
     */
    public function send();
    
    /**
     * @return string
     */
    public function __toString();
    
    /**
     * Prepare error response
     * 
     * @param \Exception $fault
     */
    public function setError(\Exception $fault);
}