<?php

namespace Bdf\Api;

use Bdf\Api\Exception\InvalidArgumentException;

/**
 * Api service
 * 
 * manage api business.
 * Available tags
 * - api-name       : map api name on definition builder. Default class short name
 * - api-action     : definition of api method
 * - api-ignore     : ignore a complex type attribute
 * - api-optional   : attribute is optional
 *
 * @package Bdf\Api
 * @deprecated Seems unused
 */
abstract class ApiService
{
    /**
     * @var array
     */
    protected static $classmap;
    
    /**
     * User custom headers
     * 
     * <pre>
     * exemple : [
     *      '__all' => ['HeaderName' => Required (true or false)],
     *      '__exceptions' => ['method' => ['HeaderName']],
     *      
     *      'methodName' => ['HeaderName' => Required (true or false)]
     * ]
     * </pre>
     * 
     * @var array
     */
    protected $applicablesHeaders = array();
    
    /**
     * @var ApiRequest
     */
    protected $request;
    
    /**
     * Permet de forcer un classmap sans avoir de classe fille
     * (utilisé uniquement par le client soap post smartphone)
     * @param array $classMap
     */
    public static function setClassmap($classMap)
    {
        static::$classmap = $classMap;
    }
    
    /**
     * @return array
     */
    public static function getClassmap()
    {
        if (null == self::$classmap) {
            self::$classmap = static::$classmap;
        }
        
        return self::$classmap ?: array();
    }

    public function __construct(?ApiRequest $request = null)
    {
        $this->request = $request;
    }
    
    /**
     * Set http request
     * 
     * @param ApiRequest $request
     * 
     * @return self
     */
    public function setRequest(ApiRequest $request)
    {
        $this->request = $request;
        
        return $this;
    }
    
    /**
     * Get http request
     * 
     * @return ApiRequest
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Handle a server request
     * 
     * @param array $request
     * 
     * @return mixed
     */
    public function __handleRequest(array $request)
    {
        $applicablesHeaders = $this->getApplicablesHeaders($request['method']['name']);

        if (count($applicablesHeaders)) {
            $requiredHeaders = array_filter($applicablesHeaders);

            foreach ($request['headers'] as $header) {
                if (isset($applicablesHeaders[$header['name']])) {
                    if (!method_exists($this, $header['name'])) {
                        throw new InvalidArgumentException('No method found for the "' . $header['name'] . '" header.');
                    }

                    unset($requiredHeaders[$header['name']]);

                    $this->{$header['name']}(...$header['arguments']);
                }
            }

            if (count($requiredHeaders)) {
                throw new InvalidArgumentException('Expected Headers: ' . implode(', ', array_keys($applicablesHeaders)) . '.');
            }
        }

        return $this->{$request['method']['name']}(...$request['method']['arguments']);
    }
    
    /**
     * Get user defined headers
     * 
     * @param string $method
     * 
     * @return array
     */
    protected function getApplicablesHeaders($method)
    {
        $headers = array();

        if (isset($this->applicablesHeaders['__all'])) {
            $headers = $this->applicablesHeaders['__all'] + $headers;
        }
        
        if (isset($this->applicablesHeaders[$method])) {
            $headers = $this->applicablesHeaders[$method] + $headers;
        }

        if (isset($this->applicablesHeaders['__exceptions'], $this->applicablesHeaders['__exceptions'][$method])) {
            $headers = array_diff_key($headers, array_flip($this->applicablesHeaders['__exceptions'][$method]));
        }
        
        return $headers;
    }
}
