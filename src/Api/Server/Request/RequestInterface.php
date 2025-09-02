<?php

namespace Bdf\Api\Server\Request;

/**
 * 
 */
interface RequestInterface
{
    /**
     * Prepare request for handling
     */
    public function prepare();
    
    /**
     * Validate the request. Throw exception to stop execution
     * 
     * @throws \Exception
     */
    public function validate();
    
    /**
     * Set the name of service method
     * 
     * @param string $method
     */
    public function setMethod($method);
    
    /**
     * Return the name of service method to call
     * 
     * @return string
     */
    public function getMethod();
    
    /**
     * Set array of parameters for service method
     * 
     * @param array $params
     */
    public function setParams(array $params);
    
    /**
     * Return array of parameters of service method
     * 
     * @return array
     */
    public function getParams();
    
    /**
     * Add a parameter to the request
     * 
     * @param  mixed $value
     * @param  string $key
     */
    public function addParam($value, $key = null);
    
    /**
     * Retrieve param by index or key
     *
     * @param  int|string $index
     * @return mixed|null Null when not found
     */
    public function getParam($index);
    
    /**
     * Has param by index or key
     *
     * @param  int|string $index
     * @return bool
     */
    public function hasParam($index);
        
    /**
     * @return string
     */
    public function __toString();
}
